<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Services\SaleService;
use App\Http\Validators\SaleValidator;
use App\Models\Sale;
use Illuminate\Http\Request;

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
}
