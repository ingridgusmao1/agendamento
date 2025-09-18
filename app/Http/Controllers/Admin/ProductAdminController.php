<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Services\ProductService;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Validators\ProductValidator;

class ProductAdminController extends Controller
{
    /** Quantidade padrão e limites para paginação */
    private const PER_PAGE_DEFAULT = 15;
    private const PER_PAGE_MIN     = 3;
    private const PER_PAGE_MAX     = 20;

    public function __construct(private ProductService $service) {}

    public function index()
    {
        // Renderiza a view principal; a tabela é preenchida via AJAX (route 'admin.products.fetch').
        return view('admin.products.index');
    }

    public function fetch(Request $request)
    {
        try {
            $q    = (string) $request->query('q', '');
            $page = max(1, (int) $request->query('page', 1));

            // aceita per_page OU perPage; aplica limites usando as CONSTs
            $perPage = (int) ($request->query('per_page', $request->query('perPage', self::PER_PAGE_DEFAULT)));
            $perPage = max(self::PER_PAGE_MIN, min(self::PER_PAGE_MAX, $perPage));

            $payload = $this->service->fetch($q, $page, $perPage);
            // $payload esperado: ['html','total','page','perPage','hasMore']

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
                'per_page' => $respPerPage, // alias para o front
                'hasMore'  => $hasMore,
                'hasPrev'  => $hasPrev,
                'hasNext'  => $hasNext,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Products fetch failed', ['e' => $e]);
            // Retorna estrutura consistente para o front não quebrar
            return response()->json([
                'html'     => view('admin.products._rows', ['items' => collect()])->render(),
                'total'    => 0,
                'page'     => 1,
                'perPage'  => self::PER_PAGE_DEFAULT,
                'per_page' => self::PER_PAGE_DEFAULT,
                'hasMore'  => false,
                'hasPrev'  => false,
                'hasNext'  => false,
                'error'    => 'fetch_failed',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        if ($request->hasFile('photos')) {
            foreach ((array) $request->file('photos') as $i => $file) {
                if ($file === null) {
                    \Log::warning('Upload null', ['index' => $i]);
                    continue;
                }
                \Log::warning('Upload debug', [
                    'index'   => $i,
                    'isValid' => $file->isValid(),
                    'err'     => $file->getError(),          // código PHP UPLOAD_ERR_*
                    'msg'     => $file->getErrorMessage(),   // mensagem humana
                    'size'    => $file->getSize(),
                    'mime'    => $file->getMimeType(),
                    'orig'    => $file->getClientOriginalName(),
                ]);
            }
        }
        // Regras de STORE (não use rulesForUpdate aqui)
        $validator = Validator::make($request->all(), ProductValidator::rulesForStore());
        if ($validator->fails()) {
            \Log::warning('Product store validation failed', $validator->errors()->toArray());
        }
        $validator->validate();

        $product = $this->service->store($request);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'id' => $product->id]);
        }
        return redirect()->route('admin.products.index')->with('success', __('global.saved'));
    }

    public function update(Request $request, Product $product)
    {
        if ($request->hasFile('photos')) {
            foreach ((array) $request->file('photos') as $i => $file) {
                if ($file === null) {
                    \Log::warning('Upload null', ['index' => $i]);
                    continue;
                }
                \Log::warning('Upload debug', [
                    'index'   => $i,
                    'isValid' => $file->isValid(),
                    'err'     => $file->getError(),          // código PHP UPLOAD_ERR_*
                    'msg'     => $file->getErrorMessage(),   // mensagem humana
                    'size'    => $file->getSize(),
                    'mime'    => $file->getMimeType(),
                    'orig'    => $file->getClientOriginalName(),
                ]);
            }
        }
        $validator = Validator::make($request->all(), ProductValidator::rulesForUpdate($product->id));
        if ($validator->fails()) {
            \Log::warning('Product update validation failed', $validator->errors()->toArray());
        }
        $validator->validate();

        // (Opcional) remoção de imagens marcadas por índice ou path
        $remove = $request->input('remove_photos', []);
        if (!empty($remove)) {
            $this->service->removeImages($product, $remove);
        }

        $this->service->update($product, $request);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }
        return redirect()->route('admin.products.index')->with('success', __('global.saved'));
    }

    public function destroy(Product $product)
    {
        // (Opcional) apaga arquivos antes de deletar o produto
        $paths = $product->photo_path ?? [];
        foreach ($paths as $p) {
            if ($p) {
                \Storage::disk('public')->delete($p);
            }
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', __('global.deleted'));
    }
}
