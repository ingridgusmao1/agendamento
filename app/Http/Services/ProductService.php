<?php

namespace App\Http\Services;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;

class ProductService
{
    public function __construct(private ViewFactory $view) {}

    /** Listagem com paginação server-side para o fetch AJAX */
    public function fetch(array $filters, int $perPage = 20): LengthAwarePaginatorContract
    {
        $q = trim((string)($filters['q'] ?? ''));

        return Product::query()
            ->when($q !== '', function ($qb) use ($q) {
                $qb->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('model', 'like', "%{$q}%")
                      ->orWhere('color', 'like', "%{$q}%")
                      ->orWhere('size',  'like', "%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage)
            ->appends(['q' => $q, 'per_page' => $perPage]);
    }

    /** Cria produto + salva fotos (se enviadas) */
    public function store(Request $request): Product
    {
        return DB::transaction(function () use ($request) {
            $data = $request->only(['name','model','color','size','price','notes','complements']);
            /** @var Product $product */
            $product = Product::create($data);

            $files = $this->gatherFiles($request);
            $saved = $this->savePhotos($product, $files);

            if ($saved) {
                $merged = array_slice(array_merge($product->photo_path ?? [], $saved), 0, 10);
                $product->photo_path = $merged;
                $product->save();
            }

            return $product;
        });
    }

    /** Atualiza produto + adiciona fotos (se enviadas) */
    public function update(Product $product, Request $request): Product
    {
        return DB::transaction(function () use ($product, $request) {
            $data = $request->only(['name','model','color','size','price','notes','complements']);
            $product->fill($data)->save();

            $files = $this->gatherFiles($request);
            $saved = $this->savePhotos($product, $files);

            if ($saved) {
                $merged = array_slice(array_merge($product->photo_path ?? [], $saved), 0, 10);
                $product->photo_path = $merged;
                $product->save();
            }

            return $product;
        });
    }

    /** Remove imagens por índice (0-based) ou por caminho */
    public function removeImages(Product $product, array $indexesOrPaths): void
    {
        $current = $product->photo_path ?? [];
        if (!$current) return;

        $toKeep = [];
        foreach ($current as $idx => $path) {
            $matchByIndex = in_array($idx, $indexesOrPaths, true);
            $matchByPath  = in_array($path, $indexesOrPaths, true);
            if ($matchByIndex || $matchByPath) {
                Storage::disk('public')->delete($path);
                Log::info('Deleted product photo', ['product_id' => $product->id, 'path' => $path]);
            } else {
                $toKeep[] = $path;
            }
        }

        $product->photo_path = array_values($toKeep);
        $product->save();
    }

    /** Coleta robusta de arquivos do input photos[] */
    private function gatherFiles(Request $request): array
    {
        $files = $request->file('photos');

        if ($files === null) {
            Log::debug('No files on request (photos is null)');
            return [];
        }
        if ($files instanceof UploadedFile) {
            return [$files];
        }
        if (is_array($files)) {
            return array_values(array_filter($files, fn($f) => $f instanceof UploadedFile));
        }
        return [];
    }

    /** Salva arquivos no disk public/products com nome: {ID}{NOMESEMACESNTOSMAIUSCULO}{#}.{ext} */
    private function savePhotos(Product $product, array $files): array
    {
        $saved = [];
        if (!$files) return $saved;

        $baseName = $this->slugUpper($product->name);
        $current  = $product->photo_path ?? [];
        $nextNum  = count($current) + 1;

        foreach ($files as $i => $file) {
            // respeita limite de 10 no total
            if (count($current) + count($saved) >= 10) break;

            $ext  = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $num  = $nextNum + $i;
            $name = "{$product->id}{$baseName}{$num}.{$ext}";
            $path = $file->storeAs('products', $name, 'public');

            Log::info('Saved product photo', ['product_id' => $product->id, 'file' => $name, 'path' => $path]);
            $saved[] = $path;
        }

        return $saved;
    }

    /** Remove acentos/espacos, deixa alfanumérico e MAIÚSCULO */
    private function slugUpper(string $name): string
    {
        $ascii = Str::ascii($name);
        $only  = preg_replace('/[^A-Za-z0-9]+/', '', $ascii) ?: '';
        return strtoupper($only);
    }

    /** Adição de fotos via galeria */
    public function addPhotos(Product $product, Request $request): int
    {
        $files = $this->gatherFiles($request);
        $saved = $this->savePhotos($product, $files);

        if ($saved) {
            $merged = array_slice(array_merge($product->photo_path ?? [], $saved), 0, 10);
            $product->photo_path = $merged;
            $product->save();
        }
        return count($saved);
    }

    public function qTrim(mixed $q): string
    {
        if ($q === null) return '';

        $q = (string) $q;
        $q = preg_replace('/[[:cntrl:]]+/u', '', $q) ?? $q; // remove chars de controle
        $q = preg_replace('/\s+/u', ' ', $q) ?? $q;        // colapsa whitespaces
        $q = trim($q);

        return function_exists('mb_substr') ? mb_substr($q, 0, 200) : substr($q, 0, 200);
    }
}
