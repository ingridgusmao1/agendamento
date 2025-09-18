<?php

namespace App\Http\Services;

use App\Models\Product;
use Illuminate\Contracts\View\Factory as ViewFactory;

class ProductService
{
    public function __construct(private ViewFactory $view) {}

    public function fetch(string $q, int $page, int $perPage): array
    {
        $query = Product::query()
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($x) use ($q) {
                    $like = "%{$q}%";
                    $x->where('name',  'like', $like)
                      ->orWhere('model','like', $like)
                      ->orWhere('color','like', $like)
                      ->orWhere('size',  'like', $like);
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

    /** Criação – o mutator do Model garante JSON array em complements */
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    /** Atualização – idem */
    public function update(Product $product, array $data): void
    {
        $product->update($data);
    }

    public function archive(Product $product): void
    {
        if (!$product->trashed()) {
            $product->delete();
        }
    }
}
