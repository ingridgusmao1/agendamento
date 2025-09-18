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
    public function __construct(private ProductService $service) {}

    public function index()
    {
        // Renderize sua view principal de listagem.
        // A tabela é preenchida via AJAX chamando route('admin.products.fetch').
        return view('admin.products.index');
    }

    public function fetch(Request $request)
    {
        try {
            $q       = (string) $request->get('q', '');
            $page    = (int) $request->get('page', 1);
            $perPage = (int) $request->get('perPage', 20);

            $payload = $this->service->fetch($q, $page, $perPage);

            return response()->json($payload);
        } catch (\Throwable $e) {
            \Log::error('Products fetch failed', ['e' => $e]);
            // devolve algo consistente para o front não exibir "NaN"
            return response()->json([
                'html'    => view('admin.products._rows', ['items' => collect()])->render(),
                'total'   => 0,
                'page'    => 1,
                'perPage' => (int) $request->get('perPage', 20),
                'hasMore' => false,
                'error'   => 'fetch_failed',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // Validação via seu ProductValidator (ajuste nomes de métodos conforme o seu arquivo)
        $validator = Validator::make($request->all(), ProductValidator::rulesForStore());
        $validator->validate();

        $product = $this->service->store($request->all());

        // Redirecione ou responda JSON conforme seu fluxo
        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'id' => $product->id]);
        }

        return redirect()->route('admin.products.index')
            ->with('success', __('global.saved'));
    }

    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), ProductValidator::rulesForUpdate($product->id));
        $validator->validate();

        // (Opcional) remoção de imagens marcadas
        $remove = $request->input('remove_photos', []);
        if (!empty($remove)) {
            $this->service->removeImages($product, $remove);
        }

        $product = $this->service->update($product, $request->all());

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('admin.products.index')
            ->with('success', __('global.saved'));
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
