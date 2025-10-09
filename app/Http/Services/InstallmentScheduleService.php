<?php

namespace App\Http\Services;

use App\Models\Installment;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class InstallmentScheduleService
{
    public const OVERDUE = 'overdue';
    public const TODAY   = 'today';
    public const SOON_3  = 'soon3';
    public const SOON_5  = 'soon5';
    public const NONE    = 'none';

    /**
     * Aplica o filtro de origem (all/store/external) a um query builder de Installment.
     */
    private function applyOriginFilter(Builder $query, string $origin): void
    {
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
        // 'all' = sem filtro adicional
    }

    /**
     * Mantido: lista paginada com highlight já calculado.
     */
    public function getFilteredInstallments(array $filters, int $perPage = 50): LengthAwarePaginator
    {
        $today  = Carbon::today();
        $origin = $filters['origin'] ?? 'all';

        $query = Installment::query()
            ->with([
                'sale:id,number,seller_id,gps_lat,gps_lng,customer_id',
                'sale.seller:id,store_mode',
                'sale.customer:id,name',
            ]);

        $this->applyOriginFilter($query, $origin);

        $paginator = $query
            ->orderBy('due_date', 'asc')
            ->paginate($perPage)
            ->withQueryString();

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

    /**
     * NOVO: retorna contadores estáticos por faixa de vencimento para o filtro atual.
     *
     * Regra solicitada (4 grupos):
     * - overdue: vencidos (diff < 0)
     * - upto3:   faltando de 0 a 3 dias (INCLUI hoje)  -> diff ∈ [0, 3]
     * - upto5:   faltando de 4 a 5 dias                -> diff ∈ [4, 5]
     * - normal:  > 5 dias                              -> diff > 5
     */
    public function getStaticCounts(array $filters): array
    {
        $today  = Carbon::today();
        $origin = $filters['origin'] ?? 'all';

        // buscamos somente os campos necessários para não pesar
        $query = Installment::query()->select(['id','due_date']);
        $this->applyOriginFilter($query, $origin);

        $counts = [
            'overdue' => 0,
            'upto3'   => 0,
            'upto5'   => 0,
            'normal'  => 0,
        ];

        $query->orderBy('due_date', 'asc')
              ->chunk(1000, function ($rows) use (&$counts, $today) {
                  foreach ($rows as $i) {
                      $due  = $i->due_date instanceof Carbon ? $i->due_date->copy()->startOfDay()
                                                             : Carbon::parse($i->due_date)->startOfDay();
                      $diff = $today->diffInDays($due, false);

                      if ($diff < 0) {
                          $counts['overdue']++;
                      } elseif ($diff <= 3) {
                          $counts['upto3']++;
                      } elseif ($diff <= 5) {
                          $counts['upto5']++;
                      } else {
                          $counts['normal']++;
                      }
                  }
              });

        return $counts;
    }

    public function getStaticAggregates(array $filters): array
    {
        $origin = $filters['origin'] ?? 'all';

        // Base para aplicar filtro de origem
        $base = \App\Models\Installment::query();

        // Precisamos do filtro antes da agregação
        $this->applyOriginFilter($base, $origin);

        // Agregação em SQL (MySQL/MariaDB)
        // remaining = max(amount - paid_total, 0)
        $remainingExpr = 'GREATEST(COALESCE(amount,0) - COALESCE(paid_total,0), 0)';
        $diffExpr      = 'DATEDIFF(due_date, CURDATE())';

        $row = $base->selectRaw("
            SUM(CASE WHEN {$diffExpr} < 0 THEN 1 ELSE 0 END)                                        AS c_overdue,
            SUM(CASE WHEN {$diffExpr} BETWEEN 0 AND 3 THEN 1 ELSE 0 END)                             AS c_upto3,
            SUM(CASE WHEN {$diffExpr} BETWEEN 4 AND 5 THEN 1 ELSE 0 END)                             AS c_upto5,
            SUM(CASE WHEN {$diffExpr} > 5 THEN 1 ELSE 0 END)                                         AS c_normal,

            SUM(CASE WHEN {$diffExpr} < 0 THEN {$remainingExpr} ELSE 0 END)                          AS s_overdue,
            SUM(CASE WHEN {$diffExpr} BETWEEN 0 AND 3 THEN {$remainingExpr} ELSE 0 END)              AS s_upto3,
            SUM(CASE WHEN {$diffExpr} BETWEEN 4 AND 5 THEN {$remainingExpr} ELSE 0 END)              AS s_upto5,
            SUM(CASE WHEN {$diffExpr} > 5 THEN {$remainingExpr} ELSE 0 END)                          AS s_normal
        ")->first();

        return [
            'counts' => [
                'overdue' => (int) ($row->c_overdue ?? 0),
                'upto3'   => (int) ($row->c_upto3   ?? 0),
                'upto5'   => (int) ($row->c_upto5   ?? 0),
                'normal'  => (int) ($row->c_normal  ?? 0),
            ],
            'sums' => [
                'overdue' => (float) ($row->s_overdue ?? 0),
                'upto3'   => (float) ($row->s_upto3   ?? 0),
                'upto5'   => (float) ($row->s_upto5   ?? 0),
                'normal'  => (float) ($row->s_normal  ?? 0),
            ],
        ];
    }
}
