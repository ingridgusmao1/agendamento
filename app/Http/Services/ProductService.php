<?php

namespace App\Http\Services;

use App\Models\Product;
use Illuminate\Contracts\View\Factory as ViewFactory;

class ProductService
{
    public function __construct(private ViewFactory $view) {}

    /** Query + paginação + render do partial para o fetch AJAX */
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
            'html'    => $html,
            'page'    => $page,
            'perPage' => $perPage,
            'total'   => $total,
            'hasPrev' => $page > 1,
            'hasNext' => ($page * $perPage) < $total,
        ];
    }

    /** Criação com normalização de complements (responsabilidade de negócio) */
    public function create(array $data): Product
    {
        $data['complements'] = $this->normalizeComplements($data['complements'] ?? null);
        return Product::create($data);
    }

    /** Atualização com normalização */
    public function update(Product $product, array $data): void
    {
        $data['complements'] = $this->normalizeComplements($data['complements'] ?? null);
        $product->update($data);
    }

    /** Arquivar (soft delete) */
    public function archive(Product $product): void
    {
        if (!$product->trashed()) {
            $product->delete();
        }
    }

    /** "a; b\nc" -> "a; b; c" (string) ou retorne array se o domínio exigir */
    private function normalizeComplements(?string $text): ?string
    {
        if ($text === null) return null;
        $text = str_replace(["\r\n","\n","\r"], ';', $text);
        $parts = array_map('trim', explode(';', $text));
        $parts = array_values(array_filter($parts, fn($v) => $v !== ''));
        return count($parts) ? implode('; ', $parts) : null;
    }
}
