<?php

namespace App\Http\Services;

use App\Models\Product;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(private ViewFactory $view) {}

    /**
     * Cria produto, gerando nomes e salvando até 10 imagens.
     * photo_path será um array de paths relativos ao disco 'public' (ex.: products/123NOME1.jpg).
     */
    public function store(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            /** @var UploadedFile[]|null $photos */
            $photos = $data['photos'] ?? null;
            unset($data['photos']);

            /** @var Product $product */
            $product = Product::create(Arr::only($data, [
                'name','model','color','size','price','notes','complements'
            ]));

            $paths = [];
            if ($photos) {
                $photos = array_slice($photos, 0, 10); // limite global
                $paths = $this->storePhotosForProduct($product->id, $product->name, $photos, 0);
            }

            $product->photo_path = $paths;
            $product->save();

            return $product;
        });
    }

    /**
     * Atualiza produto e anexa novas imagens respeitando o limite total de 10.
     */
    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            /** @var UploadedFile[]|null $photos */
            $photos = $data['photos'] ?? null;
            unset($data['photos']);

            $product->fill(Arr::only($data, [
                'name','model','color','size','price','notes','complements'
            ]));
            $product->save();

            $existing = $product->photo_path ?? [];
            $countExisting = count($existing);

            if ($photos && $countExisting < 10) {
                $slots = 10 - $countExisting;
                $photos = array_slice($photos, 0, $slots);

                $newPaths = $this->storePhotosForProduct($product->id, $product->name, $photos, $countExisting);
                $product->photo_path = array_values(array_filter(array_merge($existing, $newPaths)));
                $product->save();
            }

            return $product;
        });
    }

    /**
     * Armazena arquivos e retorna paths relativos (ex.: products/300VENTILADORBRITANIA1.jpg).
     * $startIndex: número de arquivos que o produto já possuía (para continuar a numeração).
     */
    private function storePhotosForProduct(int $productId, string $productName, array $files, int $startIndex = 0): array
    {
        $base = Str::upper(Str::slug($productName, '')); // remove acentos/pontuação/espaços e põe maiúsculo
        $paths = [];

        foreach (array_values($files) as $i => $file) {
            if (!$file instanceof UploadedFile) continue;

            $index = $startIndex + $i + 1; // começa em 1
            $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');

            // {ID}{NOME}{ÍNDICE}.{ext} — ex.: 300VENTILADORBRITANIA1.jpg
            $filename = "{$productId}{$base}{$index}.{$ext}";

            // Disco 'public' => 'storage/app/public/products' -> URL pública via Storage::url()
            $storedPath = $file->storeAs('products', $filename, 'public'); // retorna "products/arquivo.ext"
            $paths[] = $storedPath;
        }

        return $paths;
    }

    /**
     * Query + paginação + render do partial para o fetch AJAX
     */
    public function fetch(string $q, int $page, int $perPage): array
    {
        $query = Product::query()
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($x) use ($q) {
                    $like = "%{$q}%";
                    $x->where('name',  'like', $like)
                      ->orWhere('model','like', $like)
                      ->orWhere('color','like', $like)
                      ->orWhere('size', 'like', $like);
                });
            })
            ->orderBy('name');

        $total = (clone $query)->count();
        $items = $query->forPage($page, $perPage)->get();

        $html = $this->view->make('admin.products._rows', compact('items'))->render();

        return [
            'html'     => $html,
            'total'    => $total,
            'page'     => $page,
            'perPage'  => $perPage,
            'hasMore'  => ($page * $perPage) < $total,
        ];
    }

    /**
     * (Opcional) Remoção de imagens específicas durante o update:
     * Passe um array 'remove_photos' com paths relativos que devem ser deletados.
     */
    public function removeImages(Product $product, array $pathsToRemove): Product
    {
        return DB::transaction(function () use ($product, $pathsToRemove) {
            $existing = $product->photo_path ?? [];

            if (!empty($pathsToRemove)) {
                foreach ($pathsToRemove as $p) {
                    // remove fisicamente
                    if ($p && is_string($p)) {
                        Storage::disk('public')->delete($p);
                    }
                }
                // atualiza JSON
                $product->photo_path = array_values(array_diff($existing, $pathsToRemove));
                $product->save();
            }

            return $product;
        });
    }
}
