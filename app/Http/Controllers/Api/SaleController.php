<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Services\SaleService;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SaleController extends Controller
{
    /**
     * GET /api/sales/{sale}
     * Retorna a venda com cliente, itens, parcelas e fotos + saldo.
     */
    public function show(Sale $sale)
    {
        $sale->load([
            'customer',
            'items.product',
            'installments',   // já vem ordenado pelo model
            'photos',
        ]);

        return response()->json([
            'sale'      => $sale,
            'remaining' => $sale->remainingBalance(),
        ]);
    }

    /**
     * POST /api/sales
     * Cria venda, itens, gera parcelas e salva fotos.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->all();

            // Se items vier string JSON, normaliza
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
                'place_photo'        => ['nullable','file','image']
            ]);

            $total = collect($validated['items'])->sum(function ($i) {
                return ($i['qty'] ?? 0) * ($i['unit_price'] ?? 0);
            });

            $sale = \DB::transaction(function () use ($validated, $total, $request) {
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
                    'gps_lng'           => $validated['gps_lng'] ?? null
                ];

                $sale = Sale::create($saleData);

                foreach ($validated['items'] as $item) {
                    $itemData = [
                        'product_id' => $item['product_id'],
                        'qty'        => $item['qty'],
                        'unit_price' => $item['unit_price'],
                        'attributes' => $item['attributes'] ?? null
                    ];
                    $sale->items()->create($itemData);
                }

                // Ajuste conforme sua implementação (estático vs instância)
                if (method_exists(SaleService::class, 'createInstallments')) {
                    SaleService::createInstallments($sale);
                } else {
                    app(SaleService::class)->createInstallments($sale);
                }

                // Fotos (se enviadas)
                $map = [
                    'cliente' => 'customer_photo',
                    'lugar'   => 'place_photo'
                ];
                foreach ($map as $kind => $field) {
                    if ($request->hasFile($field)) {
                        $path = $request->file($field)->store('sales', 'public');
                        $sale->photos()->create([
                            'kind' => $kind,
                            'path' => $path
                        ]);
                    }
                }

                return $sale->load('installments');
            });

            return response()->json($sale, 201);
        } catch (\Throwable $e) {
            \Log::error('Erro ao criar venda', [
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'message' => 'Erro interno ao criar venda',
                'error'   => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine()
            ], 500);
        }
    }
}
