<?php

namespace App\Http\Services;

use App\Models\Installment;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InstallmentScheduleService
{
    public const OVERDUE = 'overdue';
    public const TODAY   = 'today';
    public const SOON_3  = 'soon3';
    public const SOON_5  = 'soon5';
    public const NONE    = 'none';

    /**
     * Filtros aceitos:
     *  - origin: 'all' | 'store' | 'external'
     */
    public function getFilteredInstallments(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $today  = Carbon::today();
        $origin = $filters['origin'] ?? 'all';
        if (!in_array($origin, ['all','store','external'], true)) {
            $origin = 'all';
        }

        $query = Installment::query()
            ->with([
                // venda, vendedor e cliente
                'sale:id,number,seller_id,gps_lat,gps_lng,customer_id',
                'sale.seller:id,store_mode',
                'sale.customer:id,name',
            ]);

        // ----------------------------
        // Filtro LOJA / EXTERNO
        // ----------------------------
        if ($origin === 'store') {
            // LOJA: gps nulos E seller.store_mode = 'loja'
            $query->whereHas('sale', function ($s) {
                $s->whereNull('gps_lat')
                  ->whereNull('gps_lng')
                  ->whereHas('seller', function ($u) {
                      $u->where('store_mode', 'loja');
                  });
            });
        } elseif ($origin === 'external') {
            // EXTERNO: tem gps OU seller.store_mode != 'loja' (ou nulo)
            $query->where(function ($q) {
                $q->whereHas('sale', function ($s) {
                    $s->whereNotNull('gps_lat')
                      ->orWhereNotNull('gps_lng');
                })->orWhereHas('sale.seller', function ($u) {
                    $u->where('store_mode', '!=', 'loja')
                      ->orWhereNull('store_mode');
                });
            });
        }
        // origin = 'all' => sem filtro adicional

        // ----------------------------
        // Paginação (preserva filtros) + ordenação
        // ----------------------------
        $paginator = $query
            ->orderBy('due_date', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        // ----------------------------
        // Marca visual (highlight) para a view
        // ----------------------------
        $paginator->getCollection()->transform(function ($i) use ($today) {
            $due  = $i->due_date instanceof Carbon ? $i->due_date->copy()->startOfDay()
                                                   : Carbon::parse($i->due_date)->startOfDay();
            $diff = $today->diffInDays($due, false);

            if     ($diff < 0)  { $i->highlight = self::OVERDUE; }
            elseif ($diff === 0){ $i->highlight = self::TODAY; }
            elseif ($diff <= 3) { $i->highlight = self::SOON_3; }
            elseif ($diff <= 5) { $i->highlight = self::SOON_5; }
            else                { $i->highlight = self::NONE; }

            return $i;
        });

        return $paginator;
    }
}
