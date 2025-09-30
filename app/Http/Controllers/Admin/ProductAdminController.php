<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Services\ProductService;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Validators\ProductValidator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class ProductAdminController extends Controller
{
    /** Quantidade padrão e limites para paginação */
    private const PER_PAGE_DEFAULT = 15;
    private const PER_PAGE_MIN     = 3;
    private const PER_PAGE_MAX     = 20;
    private const MAX_IMAGES = 10;

    public function __construct(private ProductService $service) {}

    public function index()
    {
        // Renderiza a view principal; a tabela é preenchida via AJAX (route 'admin.products.fetch').
        return view('admin.products.index');
    }

    public function fetch(Request $request, ProductService $service): JsonResponse
    {
        $request->validate(ProductValidator::fetch());

        $q       = $service->qTrim($request->query('q', ''));
        $perPage = self::PER_PAGE_DEFAULT;
        $items = $service->fetch(['q' => $q], $perPage);
        $html = view('admin.products._rows', compact('items'))->render();

        return response()->json([
            'html'     => $html,
            'page'     => $items->currentPage(),
            'per_page' => $items->perPage(),
            'total'    => $items->total(),
            'hasPrev'  => $items->currentPage() > 1,
            'hasNext'  => $items->hasMorePages(),
        ]);
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

    /** Tela da galeria */
    public function gallery(Product $product)
    {
        $paths = (array) ($product->photo_path ?? []);
        $canUpload = count($paths) < self::MAX_IMAGES;

        return view('admin.products.gallery', compact('product', 'canUpload'));
    }

    /** Feed JSON para o nanogallery2 */
    public function images(Product $product)
    {
        $paths = (array) ($product->photo_path ?? []);

        $items = collect($paths)->values()->map(function ($path, $i) {
            $url = \Storage::url($path);
            return [
                'src'   => $url,
                'srct'  => $url,
                'title' => '',
                'ID'    => (string) $i,
                'kind'  => 'image',
            ];
        });

        return response()->json([
            'items'     => $items,
            'canUpload' => count($paths) < self::MAX_IMAGES,
            'maxImages' => self::MAX_IMAGES,
        ]);
    }

    /** Upload múltiplo até 10 imagens (disk public) */
    public function uploadImages(Request $request, Product $product, ProductService $service)
    {
        $request->validate(ProductValidator::galleryUpload());

        $added = $service->addPhotos($product, $request);

        if ($added === 0) {
            return back()->with('err', trans('global.no_files_or_limit'));
        }

        return back()->with('ok', trans('global.images_uploaded_success'));
    }

    /** Remover imagem por índice do array */
    public function deleteImage(Product $product, int $index)
    {
        $paths = $product->photo_path ?? [];

        if (!is_array($paths)) {
            $paths = [];
        }

        if (!array_key_exists($index, $paths)) {
            return back()->with('err', __('global.image_not_found'));
        }

        Storage::disk('public')->delete($paths[$index]);

        unset($paths[$index]);
        $paths = array_values($paths);

        $product->update(['photo_path' => $paths]);

        return back()->with('ok', __('global.image_deleted_success'));
    }

    public function deleteImagesBatch(Request $request, Product $product, ProductService $service)
    {
        $data = $request->validate(ProductValidator::galleryDelete());
        $indexes = array_values(array_unique(array_map('intval', $data['indexes'] ?? [])));

        if (!$indexes) {
            return back()->with('err', trans('global.select_at_least_one'));
        }

        $service->removeImages($product, $indexes);

        return back()->with('ok', trans('global.image_deleted_success'));
    }
}
