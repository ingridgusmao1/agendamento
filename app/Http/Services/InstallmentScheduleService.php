<?php

namespace App\Http\Services;

use App\Models\Installment;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class InstallmentScheduleService
{
    public const OVERDUE = 'overdue';
    public const TODAY   = 'today';
    public const SOON_3  = 'soon3';
    public const SOON_5  = 'soon5';
    public const NONE    = 'none'; // > 5 dias

    /**
     * @param array{status?:string|null, origins?:array<string>} $filters
     */
    public function getFilteredInstallments(array $filters, int $perPage): LengthAwarePaginator
    {
        $today = Carbon::today();

        $query = Installment::with(['sale.customer', 'sale.seller'])
            ->where('status', '!=', 'pago');

        // ----------------------------
        // Filtro por ORIGEM (Loja / Externo)
        // ----------------------------
        // Aceita 'origins' como string|array|null e normaliza para array
        $origins = Arr::wrap($filters['origins'] ?? ['store', 'external']);

        $hasStore    = in_array('store', $origins, true);
        $hasExternal = in_array('external', $origins, true);


        if (!$hasStore && !$hasExternal) {
            // nada selecionado -> não retorna nenhum registro
            $query->whereRaw('1=0');
        } elseif ($hasStore && !$hasExternal) {
            // Somente LOJA
            $query->whereHas('sale', function ($s) {
                $s->whereNull('gps_lat')
                  ->whereNull('gps_lng')
                  ->whereHas('seller', function ($u) {
                      $u->where('store_mode', 'loja');
                  });
            });
        } elseif (!$hasStore && $hasExternal) {
            // Somente EXTERNO (complemento da regra da loja)
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
        // se ambos marcados, não filtra (mostra tudo)

        // ----------------------------
        // Filtro por FAIXA de vencimento (status visual)
        // ----------------------------
        $range = $filters['status'] ?? null;
        if ($range) {
            $start = $today->copy()->startOfDay();
            $end3  = $today->copy()->addDays(3)->endOfDay();
            $end5  = $today->copy()->addDays(5)->endOfDay();

            switch ($range) {
                case self::OVERDUE: // vencidas
                    $query->where('due_date', '<', $start);
                    break;
                case self::TODAY:   // do dia
                    $query->whereBetween('due_date', [$start, $start->copy()->endOfDay()]);
                    break;
                case self::SOON_3:  // até 3 dias
                    $query->whereBetween('due_date', [$today->copy()->addDay()->startOfDay(), $end3]);
                    break;
                case self::SOON_5:  // 4 a 5 dias
                    $query->whereBetween('due_date', [$today->copy()->addDays(4)->startOfDay(), $end5]);
                    break;
                case 'others':      // > 5 dias
                    $query->where('due_date', '>', $end5);
                    break;
            }
        }

        $paginator = $query
            ->orderBy('due_date', 'asc')
            ->paginate($perPage);

        // adiciona a classificação visual (highlight) para a view
        $paginator->getCollection()->transform(function ($i) use ($today) {
            $due  = $i->due_date instanceof Carbon ? $i->due_date->copy()->startOfDay() : Carbon::parse($i->due_date)->startOfDay();
            $diff = $today->diffInDays($due, false);

            if ($diff < 0)   { $i->highlight = self::OVERDUE; }
            elseif ($diff === 0) { $i->highlight = self::TODAY; }
            elseif ($diff <= 3)  { $i->highlight = self::SOON_3; }
            elseif ($diff <= 5)  { $i->highlight = self::SOON_5; }
            else                 { $i->highlight = self::NONE; }

            return $i;
        });

        return $paginator;
    }
}
