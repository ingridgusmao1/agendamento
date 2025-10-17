<?php

namespace App\Http\Services;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SaleReportService
{
    public function list(array $input): array
    {
        $norm = function ($value): array {
            if (is_null($value) || $value === '') return [];
            if (is_array($value)) {
                return array_values(array_filter($value, fn($v) => $v !== '' && $v !== null));
            }
            return [$value];
        };

        $f = [
            'user_name'      => $norm($input['user_name'] ?? []),
            'user_type'      => $norm($input['user_type'] ?? []),
            'store_mode'     => $norm($input['store_mode'] ?? []),
            'payment_method' => $norm($input['payment_method'] ?? []),
            'customer_city'  => $norm($input['customer_city'] ?? []),
            'product_name'   => $norm($input['product_name'] ?? []),
            'period'         => $input['period'] ?? null,
            'from'           => $input['from'] ?? null,
            'to'             => $input['to'] ?? null,
        ];

        // Query base
        $q = Sale::query()->with(['customer','seller','items.product','payments']);

        // Filtros
        if (!empty($f['user_name'])) {
            $q->whereHas('seller', function (Builder $w) use ($f) {
                $w->where(function (Builder $w2) use ($f) {
                    foreach ($f['user_name'] as $name) {
                        $w2->orWhere('name', 'like', '%'.$name.'%');
                    }
                });
            });
        }
        if (!empty($f['user_type'])) {
            $q->whereHas('seller', fn($w) => $w->whereIn('type', $f['user_type']));
        }
        if (!empty($f['store_mode'])) {
            $q->whereHas('seller', fn($w) => $w->whereIn('store_mode', $f['store_mode']));
        }
        if (!empty($f['payment_method'])) {
            $col = $this->paymentColumn();
            $q->whereHas('payments', fn($w) => $w->whereIn($col, $f['payment_method']));
        }
        if (!empty($f['customer_city'])) {
            $q->whereHas('customer', fn($w) => $w->whereIn('city', $f['customer_city']));
        }
        if (!empty($f['product_name'])) {
            $q->whereHas('items.product', fn($w) => $w->whereIn('name', $f['product_name']));
        }

        $this->applyPeriod($q, $f['period'], $f['from'], $f['to']);

        // -------- Totais DO CONJUNTO FILTRADO (não da página) --------
        // Obtemos os IDs das vendas filtradas (sem paginação)
        // Observação: para bases gigantes, dá para trocar por subqueries em SQL puro; aqui priorizei clareza.
        $saleIds = (clone $q)->select('sales.id')->pluck('sales.id');

        $totalsAll = $this->computeTotalsForSaleIds($saleIds);

        // Paginação (continua normal, só para listagem)
        $sales = $q->orderByDesc('created_at')->paginate(20)->withQueryString();

        $options = [
            'cities'          => $this->citiesOptions(),
            'products'        => $this->productNameOptions(),
            'payment_methods' => $this->paymentMethodOptions(),
            'user_types'      => $this->userTypeOptions(),
            'store_modes'     => $this->storeModeOptions(),
        ];

        $chips = $this->chips($f);

        return [
            'sales'          => $sales,
            'filters'        => $f,
            'chips'          => $chips,
            'options'        => $options,
            'payment_column' => $this->paymentColumn(),
            'totals'         => [ 'all' => $totalsAll ], // <<< AQUI: totais do conjunto filtrado
        ];
    }

    public function export(array $input): array
    {
        // --- Normalização idêntica ao list() ---
        $norm = function ($value): array {
            if (is_null($value) || $value === '') return [];
            if (is_array($value)) return array_values(array_filter($value, fn($v) => $v !== '' && $v !== null));
            return [$value];
        };

        $f = [
            'user_name'      => $norm($input['user_name'] ?? []),
            'user_type'      => $norm($input['user_type'] ?? []),
            'store_mode'     => $norm($input['store_mode'] ?? []),
            'payment_method' => $norm($input['payment_method'] ?? []),
            'customer_city'  => $norm($input['customer_city'] ?? []),
            'product_name'   => $norm($input['product_name'] ?? []),
            'period'         => $input['period'] ?? null,
            'from'           => $input['from'] ?? null,
            'to'             => $input['to'] ?? null,
        ];

        // --- Query base com os mesmos withs que o index ---
        $q = Sale::query()->with(['customer','seller','items.product','payments']);

        if (!empty($f['user_name'])) {
            $q->whereHas('seller', function (Builder $w) use ($f) {
                $w->where(function (Builder $w2) use ($f) {
                    foreach ($f['user_name'] as $name) {
                        $w2->orWhere('name', 'like', '%'.$name.'%');
                    }
                });
            });
        }
        if (!empty($f['user_type'])) {
            $q->whereHas('seller', fn($w) => $w->whereIn('type', $f['user_type']));
        }
        if (!empty($f['store_mode'])) {
            $q->whereHas('seller', fn($w) => $w->whereIn('store_mode', $f['store_mode']));
        }
        if (!empty($f['payment_method'])) {
            $col = $this->paymentColumn();
            $q->whereHas('payments', fn($w) => $w->whereIn($col, $f['payment_method']));
        }
        if (!empty($f['customer_city'])) {
            $q->whereHas('customer', fn($w) => $w->whereIn('city', $f['customer_city']));
        }
        if (!empty($f['product_name'])) {
            $q->whereHas('items.product', fn($w) => $w->whereIn('name', $f['product_name']));
        }

        $this->applyPeriod($q, $f['period'], $f['from'], $f['to']);

        // IDs do conjunto filtrado p/ totais
        $saleIds   = (clone $q)->select('sales.id')->pluck('sales.id');
        $totalsAll = $this->computeTotalsForSaleIds($saleIds);

        // Todos os registros (sem paginação) para o PDF
        $salesAll = $q->orderByDesc('created_at')->get();

        return [
            'sales'          => $salesAll,             // Collection completa
            'chips'          => $this->chips($f),      // mesmos chips
            'totals'         => [ 'all' => $totalsAll ],
            'payment_column' => $this->paymentColumn()
        ];
    }

    private function applyPeriod(Builder $q, ?string $period, ?string $from, ?string $to): void
    {
        if ($period === 'last_week') {
            $q->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()]);
        } elseif ($period === 'this_week') {
            $q->whereBetween('created_at', [now()->startOfWeek(), now()]);
        } elseif ($period === 'last_month') {
            $q->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]);
        } elseif ($period === 'this_month') {
            $q->whereBetween('created_at', [now()->startOfMonth(), now()]);
        } elseif ($period === 'last_year') {
            $q->whereBetween('created_at', [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()]);
        } elseif ($period === 'this_year') {
            $q->whereBetween('created_at', [now()->startOfYear(), now()]);
        } else {
            $fromDate = $from ? Carbon::parse($from) : (Sale::min('created_at') ?? now()->startOfDay());
            $toDate   = $to   ? Carbon::parse($to)   : now();
            $q->whereBetween('created_at', [$fromDate, $toDate]);
        }
    }

    private function chips(array $f): array
    {
        $chips = [];

        if (!empty($f['customer_city'])) {
            $chips[] = __('global.chip_cities', ['values' => implode(', ', $f['customer_city'])]);
        }
        if (!empty($f['product_name'])) {
            $chips[] = __('global.chip_products', ['values' => implode(', ', $f['product_name'])]);
        }
        if (!empty($f['payment_method'])) {
            $chips[] = __('global.chip_payment_methods', ['values' => implode(', ', $f['payment_method'])]);
        }
        if (!empty($f['user_name'])) {
            $chips[] = __('global.chip_seller_names', ['values' => implode(', ', $f['user_name'])]);
        }
        if (!empty($f['user_type'])) {
            $vals = array_map(fn($t) => __('global.seller_type_'.$t), $f['user_type']);
            $chips[] = __('global.chip_seller_types', ['values' => implode(', ', $vals)]);
        }
        if (!empty($f['store_mode'])) {
            $vals = array_map(fn($m) => __('global.store_mode_'.$m), $f['store_mode']);
            $chips[] = __('global.chip_store_modes', ['values' => implode(', ', $vals)]);
        }
        if (!empty($f['period'])) {
            $chips[] = __('global.chip_period', ['value' => __('global.'.$f['period'])]);
        } elseif (!empty($f['from']) || !empty($f['to'])) {
            $range = trim(($f['from'] ?? '').' - '.($f['to'] ?? ''), ' -');
            $chips[] = __('global.chip_period', ['value' => $range]);
        }

        return $chips;
    }

    // --- Coluna dinâmica de "método" em payments
    private function paymentColumn(): string
    {
        if (Schema::hasColumn('payments', 'method'))  return 'method';
        if (Schema::hasColumn('payments', 'gateway')) return 'gateway';
        return 'note';
    }

    private function paymentMethodOptions(): array
    {
        $col = $this->paymentColumn();
        return Payment::query()
            ->whereNotNull($col)
            ->distinct()
            ->orderBy($col)
            ->pluck($col)
            ->filter()
            ->values()
            ->all();
    }

    private function citiesOptions(): array
    {
        return Customer::query()
            ->whereNotNull('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city')
            ->filter()
            ->values()
            ->all();
    }

    private function productNameOptions(): array
    {
        return Product::query()
            ->whereNotNull('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name')
            ->filter()
            ->values()
            ->all();
    }

    private function userTypeOptions(): array
    {
        return User::query()
            ->whereNotNull('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type')
            ->filter()
            ->values()
            ->all();
    }

    private function storeModeOptions(): array
    {
        return User::query()
            ->whereNotNull('store_mode')
            ->distinct()
            ->orderBy('store_mode')
            ->pluck('store_mode')
            ->filter()
            ->values()
            ->all();
    }

    // --------- AGREGADORES DO CONJUNTO FILTRADO ---------

    /** Totais para os sales IDs informados (conjunto inteiro filtrado). */
    private function computeTotalsForSaleIds($saleIds): array
    {
        $ids = collect($saleIds)->filter()->values();
        if ($ids->isEmpty()) {
            return ['sold' => 0.0, 'received' => 0.0, 'outstanding' => 0.0];
        }

        $totalSold     = $this->sumSold($ids);
        $totalReceived = $this->sumReceived($ids);

        return [
            'sold'        => $totalSold,
            'received'    => $totalReceived,
            'outstanding' => max(0, $totalSold - $totalReceived),
        ];
    }

    /** Soma o total vendido para uma lista de sales IDs. */
    private function sumSold($saleIds): float
    {
        $ids = collect($saleIds)->values();

        // 1) se existir sales.total
        if (Schema::hasColumn('sales', 'total')) {
            return (float) Sale::query()->whereIn('id', $ids)->sum('total');
        }

        // 2) soma por items: item.total OU (price|unit_price) * (quantity|qty)
        $expr = 'COALESCE(sale_items.total, COALESCE(sale_items.price, sale_items.unit_price, 0) * COALESCE(sale_items.quantity, sale_items.qty, 1))';

        return (float) DB::table('sale_items')
            ->whereIn('sale_items.sale_id', $ids)
            ->sum(DB::raw($expr));
    }

    /** Soma o total recebido (payments.amount) para uma lista de sales IDs. */
    private function sumReceived($saleIds): float
    {
        $ids = collect($saleIds)->values();

        // Se payments tem sale_id, soma direto
        if (Schema::hasColumn('payments', 'sale_id')) {
            return (float) DB::table('payments')
                ->whereIn('payments.sale_id', $ids)
                ->sum('payments.amount');
        }

        // Caso contrário, some via installments -> sale_id
        return (float) DB::table('payments')
            ->join('installments', 'installments.id', '=', 'payments.installment_id')
            ->whereIn('installments.sale_id', $ids)
            ->sum('payments.amount');
    }
}
