<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Validators\ProductValidator;
use App\Models\Product;
use App\Http\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class ProductAdminController extends Controller
{
    private const PER_PAGE = 15;

    public function __construct(private ProductService $service) {}

    /** Página principal (filtros básicos) */
    public function index(Request $r)
    {
        $q = trim((string) $r->query('q', ''));
        return view('admin.products.index', compact('q'));
    }

    /** Endpoint AJAX para montar linhas da tabela */
    public function fetch(Request $r)
    {
        $r->validate(ProductValidator::fetch());
        $q    = trim((string)$r->query('q',''));
        $page = max(1, (int)$r->query('page',1));

        return response()->json(
            $this->service->fetch($q, $page, self::PER_PAGE)
        );
    }

    /** Criar produto */
    public function store(Request $r): RedirectResponse
    {
        $data = $r->validate(ProductValidator::store());
        $this->service->create($data);

        return back()->with('ok', __('global.product_created'));
    }

    /** Atualizar produto */
    public function update(Request $r, Product $product): RedirectResponse
    {
        $data = $r->validate(ProductValidator::update());
        $this->service->update($product, $data);

        return back()->with('ok', __('global.product_updated'));
    }

    /** Arquivar (soft delete) */
    public function destroy(Product $product): RedirectResponse
    {
        if ($product->trashed()) {
            return back()->with('ok', __('global.product_already_archived'));
        }

        $this->service->archive($product);
        return back()->with('ok', __('global.product_archived'));
    }
}
