<?php

namespace App\Http\Services;

use App\Models\{Sale, Installment};
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory as ViewFactory;

class SaleService {
    public function __construct(private ViewFactory $view) {}

    /**
     * Lista vendas com busca e paginação, renderizando as linhas via view.
     * Busca por: id da venda (#123), nome do cliente, cpf, status
     */
    public function fetch(?string $q, ?string $status, int $page, int $perPage): array
    {
        $query = Sale::query()
            ->with([
                'customer:id,name,avatar_path', // para avatar e nome
            ])
            ->orderByDesc('id');

        if ($status) {
            $query->where('status', $status);
        }

        if ($q) {
            $like = '%' . str_replace(['%','_'], ['\\%','\\_'], $q) . '%';
            $query->where(function($w) use ($q, $like) {
                // #123 → busca por id
                if (preg_match('/^\#?(\d+)$/', trim($q), $m)) {
                    $w->orWhere('id', (int)$m[1]);
                }
                $w->orWhere('status', 'like', $like)
                  ->orWhereHas('customer', function($q2) use ($like) {
                      $q2->where('name', 'like', $like)
                         ->orWhere('cpf', 'like', $like);
                  });
            });
        }

        $total = (clone $query)->count();
        $items = $query->forPage($page, $perPage)->get();

        $html = $this->view->make('admin.sales._rows', ['items' => $items])->render();

        return [
            'html'    => $html,
            'total'   => $total,
            'page'    => $page,
            'perPage' => $perPage,
            'hasMore' => $total > $page * $perPage,
        ];
    }

    /**
     * Carrega dados completos para o "Detalhes"
     */
    public function details(\App\Models\Sale $sale): string
    {
        $sale->loadMissing([
            'customer',               // id, name, avatar_path, etc.
            'items.product',          // produto usado nas linhas
            'installments.payments',  // pagamentos via parcelas
            'payments',               // atalho agregado (hasManyThrough)
        ]);

        return $this->view
            ->make('admin.sales._details', ['sale' => $sale])
            ->render();
    }

    public static function createInstallments(Sale $sale): void
    {
        $base  = Carbon::parse($sale->charge_start_date)->day($sale->due_day);
        $value = round($sale->total / $sale->installments_qty, 2);

        for ($i=1; $i <= $sale->installments_qty; $i++) {
            $due = (clone $base)->addMonthsNoOverflow($i-1);
            if ($sale->rescheduled_day) $due->day($sale->rescheduled_day);

            Installment::create([
                'sale_id'=>$sale->id,
                'number'=>$i,
                'due_date'=>$due,
                'amount'=>$value,
            ]);
        }
    }

    public function qTrim(mixed $q): string
    {
        if ($q === null) return '';

        $q = (string) $q;
        $q = preg_replace('/[[:cntrl:]]+/u', '', $q) ?? $q;
        $q = preg_replace('/\s+/u', ' ', $q) ?? $q;
        $q = trim($q);

        return function_exists('mb_substr') ? mb_substr($q, 0, 200) : substr($q, 0, 200);
    }
}
