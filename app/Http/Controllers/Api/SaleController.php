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
        // Se vier multipart/form-data e items for JSON string, normaliza:
        $items = $request->input('items');
        if (is_string($items)) {
            $decoded = json_decode($items, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request->merge(['items' => $decoded]);
            }
        }

        $data = $request->validate([
            'customer_id'                  => ['required', 'exists:customers,id'],
            'items'                        => ['required', 'array', 'min:1'],
            'items.*.product_id'           => ['required', 'exists:products,id'],
            'items.*.qty'                  => ['required', 'integer', 'min:1'],
            'items.*.unit_price'           => ['required', 'numeric', 'min:0'],
            'items.*.attributes'           => ['nullable', 'array'],

            'installments_qty'             => ['required', 'integer', 'min:1', 'max:60'],
            'due_day'                      => ['required', 'integer', 'min:1', 'max:31'],
            'rescheduled_day'              => ['nullable', 'integer', 'min:1', 'max:31'],
            'charge_start_date'            => ['required', 'date'],

            'delivery_text'                => ['required', 'string'],
            'gps_lat'                      => ['nullable', 'numeric'],
            'gps_lng'                      => ['nullable', 'numeric'],
            'collection_note'              => ['nullable', 'string'],

            // fotos opcionais (kinds em pt-BR)
            'customer_photo'               => ['nullable', 'file', 'image'],
            'place_photo'                  => ['nullable', 'file', 'image'],
        ]);

        // número da nota
        $number = now()->format('YmdHis') . '-' . substr(bin2hex(random_bytes(2)), 0, 4);

        // total calculado a partir dos itens
        $total = collect($data['items'])->sum(fn ($i) => (float)$i['qty'] * (float)$i['unit_price']);

        $sale = DB::transaction(function () use ($request, $data, $number, $total) {

            $sale = Sale::create([
                'number'            => $number,
                'customer_id'       => $data['customer_id'],
                'seller_id'         => $request->user()->id,
                'total'             => $total,
                'installments_qty'  => $data['installments_qty'],
                'due_day'           => $data['due_day'],
                'rescheduled_day'   => $data['rescheduled_day'] ?? null,
                'charge_start_date' => $data['charge_start_date'],
                'delivery_text'     => $data['delivery_text'],
                'gps_lat'           => $data['gps_lat'] ?? null,
                'gps_lng'           => $data['gps_lng'] ?? null,
                'collection_note'   => $data['collection_note'] ?? null,
                'status'            => 'open', // campo geral da venda (se usar)
            ]);

            foreach ($data['items'] as $i) {
                $sale->items()->create([
                    'product_id' => $i['product_id'],
                    'qty'        => $i['qty'],
                    'unit_price' => $i['unit_price'],
                    'attributes' => $i['attributes'] ?? null,
                ]);
            }

            // Gera parcelas conforme regras de dia/remarcação
            SaleService::createInstallments($sale);

            // Fotos opcionais (kinds 'cliente' e 'lugar')
            foreach (['cliente' => 'customer_photo', 'lugar' => 'place_photo'] as $kind => $field) {
                if ($request->hasFile($field)) {
                    $path = $request->file($field)->store('sales', 'public');
                    $sale->photos()->create(['kind' => $kind, 'path' => $path]);
                }
            }

            return $sale->load(['installments']);
        });

        return response()->json($sale, 201);
    }
}
