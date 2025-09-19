<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Services\CustomerService;
use App\Http\Validators\CustomerValidator;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerAdminController extends Controller
{
    /** Quantidade padrão e limites para paginação */
    private const PER_PAGE_DEFAULT = 15;
    private const PER_PAGE_MIN     = 5;
    private const PER_PAGE_MAX     = 100;

    public function __construct(private CustomerService $service) {}

    public function index()
    {
        // Renderiza a view; a tabela é preenchida via AJAX (route 'admin.customers.fetch')
        return view('admin.customers.index');
    }

    public function fetch(Request $request)
    {
        $request->validate(CustomerValidator::fetch());

        $q       = (string) $request->query('q', '');
        $page    = max(1, (int) $request->query('page', 1));
        $perPage = (int) ($request->query('per_page', $request->query('perPage', self::PER_PAGE_DEFAULT)));
        $perPage = max(self::PER_PAGE_MIN, min(self::PER_PAGE_MAX, $perPage));

        $payload = $this->service->fetch($q, $page, $perPage);

        $respPage    = (int)   ($payload['page']    ?? $page);
        $respPerPage = (int)   ($payload['perPage'] ?? $perPage);
        $respTotal   = (int)   ($payload['total']   ?? 0);
        $hasMore     = (bool)  ($payload['hasMore'] ?? false);
        $hasPrev     = $respPage > 1;
        $hasNext     = $hasMore;

        return response()->json([
            'html'     => $payload['html'] ?? '',
            'total'    => $respTotal,
            'page'     => $respPage,
            'perPage'  => $respPerPage,
            'hasMore'  => $hasMore,
            'hasPrev'  => $hasPrev,
            'hasNext'  => $hasNext,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(CustomerValidator::rulesForStore());
        $customer = $this->service->store($request);

        return redirect()
            ->route('admin.customers.index')
            ->with('ok', trans('global.created_success'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate(CustomerValidator::rulesForUpdate($customer->id));
        $this->service->update($customer, $request);

        return back()->with('ok', trans('global.updated_success'));
    }

    public function destroy(Customer $customer)
    {
        $this->service->destroy($customer);
        return back()->with('ok', trans('global.deleted_success'));
    }
}
