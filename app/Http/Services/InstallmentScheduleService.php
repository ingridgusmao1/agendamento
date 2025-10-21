<?php

namespace App\Http\Services;

use App\Models\Installment;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
    // App/Http/Services/InstallmentScheduleService.php
    public function getFilteredInstallments(array $filters, int $perPage): LengthAwarePaginator
    {
        $today  = Carbon::today();
        $origin = $filters['origin'] ?? 'all';
        $cat    = $filters['cat'] ?? null;

        /** @var Builder $q */
        $q = Installment::query()
            // MUITO IMPORTANTE: garante sale_id e demais colunas do installment
            ->select('installments.*')
            // Eager loading só do que precisamos no modal + tabela
            ->with([
                'sale:id,number,collection_note,gps_lat,gps_lng,customer_id',
                'sale.customer:id,name,street,number,district,city,reference_point,phone',
            ])
            // Campos calculados usados pela view
            ->addSelect([
                DB::raw("
                    CASE
                        WHEN DATEDIFF(due_date, CURDATE()) < 0 THEN 'overdue'
                        WHEN DATEDIFF(due_date, CURDATE()) = 0 THEN 'today'
                        WHEN DATEDIFF(due_date, CURDATE()) BETWEEN 1 AND 3 THEN 'soon3'
                        WHEN DATEDIFF(due_date, CURDATE()) BETWEEN 4 AND 5 THEN 'soon5'
                        ELSE 'normal'
                    END AS highlight
                "),
                DB::raw("GREATEST(COALESCE(amount,0) - COALESCE(paid_total,0), 0) AS remaining"),
            ]);

        // Filtro de origem via relações (NÃO use where('origin',...))
        $this->applyOriginFilter($q, $origin);

        // Filtro de categoria (coerente com o CASE acima)
        if ($cat === self::TODAY) {
            $q->whereDate('due_date', $today);
        } elseif ($cat === self::OVERDUE) {
            $q->whereDate('due_date', '<', $today);
        } elseif ($cat === self::SOON_3) {
            $q->whereRaw('DATEDIFF(due_date, CURDATE()) BETWEEN 1 AND 3');
        } elseif ($cat === self::SOON_5) {
            $q->whereRaw('DATEDIFF(due_date, CURDATE()) BETWEEN 4 AND 5');
        } elseif ($cat === self::NONE) {
            $q->whereRaw('DATEDIFF(due_date, CURDATE()) > 5');
        }

        // Hoje primeiro
        return $q->orderByRaw("CASE WHEN DATEDIFF(due_date, CURDATE()) = 0 THEN 0 ELSE 1 END ASC")
                ->orderBy('due_date')
                ->paginate($perPage)
                ->withQueryString();
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

        $base = Installment::query();                 // <— não selecione colunas aqui
        $this->applyOriginFilter($base, $origin);     // mantém seu filtro de origem

        $remainingExpr = 'GREATEST(COALESCE(amount,0) - COALESCE(paid_total,0), 0)';
        $diffExpr      = 'DATEDIFF(due_date, CURDATE())';

        $row = $base->selectRaw("
            SUM(CASE WHEN {$diffExpr} = 0 THEN 1 ELSE 0 END)                           AS c_today,
            SUM(CASE WHEN {$diffExpr} = 0 THEN {$remainingExpr} ELSE 0 END)            AS s_today,

            SUM(CASE WHEN {$diffExpr} < 0 THEN 1 ELSE 0 END)                           AS c_overdue,
            SUM(CASE WHEN {$diffExpr} < 0 THEN {$remainingExpr} ELSE 0 END)            AS s_overdue,

            SUM(CASE WHEN {$diffExpr} BETWEEN 1 AND 3 THEN 1 ELSE 0 END)               AS c_upto3,
            SUM(CASE WHEN {$diffExpr} BETWEEN 1 AND 3 THEN {$remainingExpr} ELSE 0 END) AS s_upto3,

            SUM(CASE WHEN {$diffExpr} BETWEEN 4 AND 5 THEN 1 ELSE 0 END)               AS c_upto5,
            SUM(CASE WHEN {$diffExpr} BETWEEN 4 AND 5 THEN {$remainingExpr} ELSE 0 END) AS s_upto5,

            SUM(CASE WHEN {$diffExpr} > 5 THEN 1 ELSE 0 END)                           AS c_normal,
            SUM(CASE WHEN {$diffExpr} > 5 THEN {$remainingExpr} ELSE 0 END)            AS s_normal
        ")->first();

        // Protege contra nulls quando não há linhas
        return [
            'counts' => [
                'today'   => (int) ($row->c_today   ?? 0),
                'overdue' => (int) ($row->c_overdue ?? 0),
                'upto3'   => (int) ($row->c_upto3   ?? 0),
                'upto5'   => (int) ($row->c_upto5   ?? 0),
                'normal'  => (int) ($row->c_normal  ?? 0),
            ],
            'sums' => [
                'today'   => (float) ($row->s_today   ?? 0),
                'overdue' => (float) ($row->s_overdue ?? 0),
                'upto3'   => (float) ($row->s_upto3   ?? 0),
                'upto5'   => (float) ($row->s_upto5   ?? 0),
                'normal'  => (float) ($row->s_normal  ?? 0),
            ],
        ];
    }
}
