<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Services\SaleService;
use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class SaleController extends Controller
{
    public function show(Sale $sale)
    {
        // Sem 'photos'
        $sale->load(['customer', 'items.product', 'installments']);

        return response()->json([
            'sale'      => $sale,
            'remaining' => $sale->remainingBalance(),
        ]);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all();

            // Itens podem vir como string JSON; normaliza
            if (is_string($data['items'] ?? null)) {
                $decoded = json_decode($data['items'], true);
                $data['items'] = is_array($decoded) ? $decoded : [];
                $request->merge(['items' => $data['items']]);
            }

            // Validação inicial
            $validated = $request->validate([
                'customer_id'        => ['required','exists:customers,id'],
                'items'              => ['required','array','min:1'],
                'items.*.product_id' => ['required','exists:products,id'],
                'items.*.qty'        => ['required','integer','min:1'],
                'items.*.unit_price' => ['required','numeric','min:0'],
                'items.*.attributes'   => ['nullable','array'],
                'items.*.attributes.*' => ['string'],
                'installments_qty'   => ['required','integer','min:1'],
                'due_day'            => ['required','integer','between:1,31'],
                'rescheduled_day'    => ['nullable','integer','between:1,31'],
                'charge_start_date'  => ['required','date'],
                'delivery_text'      => ['nullable','string'],
                'note'               => ['nullable','string'],
                'gps_lat'            => ['nullable','numeric'],
                'gps_lng'            => ['nullable','numeric'],
                'customer_photo'     => ['nullable','image','max:5120'],
                'place_photo'        => ['nullable','image','max:5120'],
            ]);

            // Validação extra: atributos devem ser escolhidos se o produto tiver complements
            $productIds = collect($validated['items'])->pluck('product_id')->all();
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            $errors = [];
            foreach ($validated['items'] as $idx => $it) {
                $prod = $products[$it['product_id']] ?? null;
                $complements = is_array($prod?->complements) ? $prod->complements : [];

                if (!empty($complements)) {
                    $attrs = $it['attributes'] ?? [];
                    if (!is_array($attrs) || count($attrs) < 1) {
                        $errors["items.$idx.attributes"] = 'Selecione ao menos um complemento para este produto.';
                        continue;
                    }
                    $invalid = array_diff($attrs, $complements);
                    if (!empty($invalid)) {
                        $errors["items.$idx.attributes"] = 'Complemento inválido para o produto selecionado.';
                    }
                }
            }

            if (!empty($errors)) {
                return response()->json([
                    'message' => 'Erro de validação nos itens',
                    'errors'  => $errors,
                ], 422);
            }

            // Calcula total
            $total = collect($validated['items'])
                ->sum(fn($i) => (int)$i['qty'] * (float)$i['unit_price']);

            $sale = DB::transaction(function () use ($validated, $total, $request) {
                // 1) Criar a venda
                $sale = Sale::create([
                    'customer_id'       => $validated['customer_id'],
                    'seller_id'         => auth()->id(),
                    'total'             => $total,
                    'installments_qty'  => $validated['installments_qty'],
                    'due_day'           => $validated['due_day'],
                    'rescheduled_day'   => $validated['rescheduled_day'] ?? null,
                    'charge_start_date' => $validated['charge_start_date'],
                    'delivery_text'     => $validated['delivery_text'] ?? null,
                    'collection_note'   => $validated['note'] ?? null,
                    'status'            => Sale::STATUS_OPEN,
                    'gps_lat'           => $validated['gps_lat'] ?? null,
                    'gps_lng'           => $validated['gps_lng'] ?? null,
                ]);

                // 2) Itens
                foreach ($validated['items'] as $item) {
                    $sale->items()->create([
                        'product_id' => $item['product_id'],
                        'qty'        => $item['qty'],
                        'unit_price' => $item['unit_price'],
                        'attributes' => $item['attributes'] ?? [],
                    ]);
                }

                // 3) Ajuste de estoque por item (aceita qty negativa → aumenta o estoque)
                foreach ($validated['items'] as $item) {
                    $pid = (int) $item['product_id'];
                    $qty = (int) $item['qty'];

                    // lock pessimista para evitar corrida de estoque
                    $prod = Product::whereKey($pid)->lockForUpdate()->first();

                    if (!$prod) {
                        throw new \RuntimeException("Produto {$pid} não encontrado para ajuste de estoque.");
                    }

                    $prod->increment('stock_total', -$qty);
                }

                // 4) Parcelas
                if (method_exists(SaleService::class, 'createInstallments')) {
                    SaleService::createInstallments($sale);
                } else {
                    app(SaleService::class)->createInstallments($sale);
                }

                // 5) Atualizar fotos do cliente, se existirem
                $customer = Customer::find($sale->customer_id);

                if ($request->hasFile('customer_photo')) {
                    $filename = $this->customerFilename($customer->name, (int)$customer->id);
                    $cPath    = $request->file('customer_photo')->storeAs('customers', $filename, 'public');
                    $customer->avatar_path = $cPath;
                }

                if ($request->hasFile('place_photo')) {
                    $lat      = (float)($validated['gps_lat'] ?? $customer->lat ?? 0);
                    $lng      = (float)($validated['gps_lng'] ?? $customer->lng ?? 0);
                    $filename = $this->placeFilename($lat, $lng, (int)$customer->id);
                    $pPath    = $request->file('place_photo')->storeAs('places', $filename, 'public');
                    $customer->place_path = $pPath;
                }

                $customer->save();

                return $sale->load('installments');
            });

            return response()->json($sale, 201);

        } catch (\Throwable $e) {
            \Log::error('Erro ao criar venda', [
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'message' => 'Erro interno ao criar venda',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // --------------- Helpers de nome (iguais ao CustomerController) ---------------

    private function customerFilename(string $name, int $id): string
    {
        $slug = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::slug($name, '-'));
        return "{$slug}-{$id}.jpg";
    }

    private function coordToken(float $v): string
    {
        $sign = $v < 0 ? 'M' : 'P';
        $abs  = abs($v);
        [$i, $d] = explode('.', sprintf('%.4f', $abs));
        return "{$sign}{$i}D{$d}";
    }

    private function placeFilename(float $lat, float $lng, int $id): string
    {
        return $this->coordToken($lat).'-'.$this->coordToken($lng)."-{$id}.jpg";
    }
}
