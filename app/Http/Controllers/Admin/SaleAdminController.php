<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Services\SaleService;
use App\Http\Validators\SaleValidator;
use App\Models\Sale;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SaleAdminController extends Controller
{
    private const PER_PAGE_DEFAULT = 15;
    private const PER_PAGE_MIN     = 5;
    private const PER_PAGE_MAX     = 100;

    public function __construct(private SaleService $service) {}

    public function index()
    {
        return view('admin.sales.index');
    }

    public function fetch(Request $request)
    {
        $request->validate(SaleValidator::fetch());

        $q       = (string) $request->query('q', '');
        $status  = $request->query('status');
        $page    = max(1, (int) $request->query('page', 1));
        $perPage = (int) ($request->query('per_page', $request->query('perPage', self::PER_PAGE_DEFAULT)));
        $perPage = max(self::PER_PAGE_MIN, min(self::PER_PAGE_MAX, $perPage));

        $payload = $this->service->fetch($q, $status, $page, $perPage);

        return response()->json([
            'html'     => $payload['html'] ?? '',
            'total'    => (int) ($payload['total']   ?? 0),
            'page'     => (int) ($payload['page']    ?? $page),
            'perPage'  => (int) ($payload['perPage'] ?? $perPage),
            'hasMore'  => (bool)($payload['hasMore'] ?? false),
            'hasPrev'  => ($payload['page'] ?? $page) > 1,
            'hasNext'  => (bool)($payload['hasMore'] ?? false),
        ]);
    }

    /** Retorna HTML do modal de detalhes */
    public function show(Sale $sale)
    {
        $html = $this->service->details($sale);
        return response()->json(['html' => $html]);
    }

    public function financialReports(Request $request)
    {
        $q = Sale::query()->with(['customer','seller','items.product','payments']);

        // Filtros
        if ($request->filled('user_name')) {
            $q->whereHas('seller', fn($w) => $w->where('name','like','%'.$request->user_name.'%'));
        }
        if ($request->filled('user_type')) {
            $q->whereHas('seller', fn($w) => $w->where('type',$request->user_type));
        }
        if ($request->filled('store_mode')) {
            $q->whereHas('seller', fn($w) => $w->where('store_mode',$request->store_mode));
        }
        if ($request->filled('payment_method')) {
            $q->whereHas('payments', fn($w) => $w->where('method',$request->payment_method));
        }
        if ($request->filled('customer_city')) {
            $q->whereHas('customer', fn($w) => $w->where('city','like','%'.$request->customer_city.'%'));
        }
        if ($request->filled('product_name')) {
            $q->whereHas('items.product', fn($w) => $w->where('name','like','%'.$request->product_name.'%'));
        }

        // Filtro de datas (pré-definidos ou custom)
        $period = $request->input('period');
        $from   = $request->input('from');
        $to     = $request->input('to');

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
            // intervalo manual
            $fromDate = $from ? Carbon::parse($from) : Sale::min('created_at');
            $toDate   = $to   ? Carbon::parse($to)   : now();
            $q->whereBetween('created_at', [$fromDate,$toDate]);
        }

        $sales = $q->orderByDesc('created_at')->paginate(20);

        return view('admin.financial_reports.index', [
            'sales'   => $sales,
            'filters' => $request->all(),
        ]);
    }

}
