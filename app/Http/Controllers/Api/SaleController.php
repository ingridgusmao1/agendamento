<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Services\SaleService;
use App\Models\Sale;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SaleController extends Controller
{
    public function show(Sale $sale)
    {
        $sale->load(['customer','items.product','installments']);
        return response()->json([
            'sale'      => $sale,
            'remaining' => $sale->remainingBalance(),
        ]);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all();

            if (is_string($data['items'] ?? null)) {
                $decoded = json_decode($data['items'], true);
                $data['items'] = is_array($decoded) ? $decoded : [];
                $request->merge(['items' => $data['items']]);
            }

            $validated = $request->validate([
                'customer_id'        => ['required','exists:customers,id'],
                'items'              => ['required','array','min:1'],
                'items.*.product_id' => ['required','exists:products,id'],
                'items.*.qty'        => ['required','integer','min:1'],
                'items.*.unit_price' => ['required','numeric','min:0'],
                'installments_qty'   => ['required','integer','min:1'],
                'due_day'            => ['required','integer','min:1','max:31'],
                'charge_start_date'  => ['required','date'],
                'delivery_text'      => ['nullable','string'],
                'note'               => ['nullable','string'],
                'rescheduled_day'    => ['nullable','integer','min:1','max:31'],
                'gps_lat'            => ['nullable','numeric'],
                'gps_lng'            => ['nullable','numeric'],
                'customer_photo'     => ['nullable','file','image'],
                'place_photo'        => ['nullable','file','image'],
            ]); // :contentReference[oaicite:2]{index=2}

            $total = collect($validated['items'])->sum(fn ($i) => ($i['qty'] ?? 0) * ($i['unit_price'] ?? 0));

            $sale = DB::transaction(function () use ($validated, $total, $request) {

                $saleData = [
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
                ];

                $sale = Sale::create($saleData);

                foreach ($validated['items'] as $item) {
                    $sale->items()->create([
                        'product_id' => $item['product_id'],
                        'qty'        => $item['qty'],
                        'unit_price' => $item['unit_price'],
                        'attributes' => $item['attributes'] ?? null,
                    ]);
                }

                // Parcelas (estático ou via instância, como você já faz) :contentReference[oaicite:3]{index=3}
                if (method_exists(SaleService::class, 'createInstallments')) {
                    SaleService::createInstallments($sale);
                } else {
                    app(SaleService::class)->createInstallments($sale);
                }

                // ADIÇÃO: se chegar customer_photo/place_photo, atualizar o Customer também.
                $customer = Customer::find($sale->customer_id);

                if ($request->hasFile('customer_photo')) {
                    $cPath = $request->file('customer_photo')->store('customers', 'public');
                    $customer->avatar_path = $cPath;
                }

                if ($request->hasFile('place_photo')) {
                    $lat = (float)($validated['gps_lat'] ?? $customer->lat ?? 0);
                    $lng = (float)($validated['gps_lng'] ?? $customer->lng ?? 0);
                    $filename = $this->placeFilename($lat, $lng, (int)$customer->id);
                    $pPath = $request->file('place_photo')->storeAs('places', $filename, 'public');
                    $customer->place_path = $pPath;
                }

                $customer->save();

                return $sale->load('installments');
            });

            return response()->json($sale, 201);

        } catch (\Throwable $e) {
            \Log::error('Erro ao criar venda', ['msg' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
            return response()->json([
                'message' => 'Erro interno ao criar venda',
                'error'   => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ], 500);
        }
    }

    private function coordToken(float $v): string
    {
        $sign = $v < 0 ? 'M' : 'P';
        $abs  = abs($v);
        $formatted = sprintf('%.4f', $abs);
        [$i,$d] = explode('.', $formatted);
        return "{$sign}{$i}D{$d}";
    }

    private function placeFilename(float $lat, float $lng, int $id): string
    {
        return $this->coordToken($lat) . '-' . $this->coordToken($lng) . "-{$id}.jpg";
    }
}
