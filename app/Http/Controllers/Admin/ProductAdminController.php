<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductAdminController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $r)
    {
        // A tabela carrega via AJAX (fetch)
        $q = trim((string)$r->query('q', ''));
        return view('admin.products.index', compact('q'));
    }

    public function fetch(Request $r)
    {
        $q       = trim((string) $r->query('q', ''));
        $page    = max(1, (int) $r->query('page', 1));
        $perPage = self::PER_PAGE;

        $query = Product::query()
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($x) use ($q) {
                    $x->where('name',  'like', "%{$q}%")
                      ->orWhere('model','like', "%{$q}%")
                      ->orWhere('color','like', "%{$q}%")
                      ->orWhere('size', 'like', "%{$q}%");
                });
            })
            ->orderBy('name');

        $total = (clone $query)->count();
        $items = $query->forPage($page, $perPage)->get();

        $html = view('admin.products._rows', compact('items'))->render();

        return response()->json([
            'html'    => $html,
            'page'    => $page,
            'perPage' => $perPage,
            'total'   => $total,
            'hasPrev' => $page > 1,
            'hasNext' => ($page * $perPage) < $total,
        ]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name'        => 'required|string|max:160',
            'model'       => 'nullable|string|max:160',
            'color'       => 'nullable|string|max:100',
            'size'        => 'nullable|string|max:100',
            'price'       => 'required|numeric|min:0',
            'notes'       => 'nullable|string',
            'photo_path'  => 'nullable|string|max:255',
            'complements' => 'nullable|string', // texto separado por ';'
        ]);

        $data['complements'] = $this->normalizeComplements($data['complements'] ?? null);

        Product::create($data);

        return back()->with('ok', __('global.product_created'));
    }

    public function update(Request $r, Product $product)
    {
        $data = $r->validate([
            'name'        => 'required|string|max:160',
            'model'       => 'nullable|string|max:160',
            'color'       => 'nullable|string|max:100',
            'size'        => 'nullable|string|max:100',
            'price'       => 'required|numeric|min:0',
            'notes'       => 'nullable|string',
            'photo_path'  => 'nullable|string|max:255',
            'complements' => 'nullable|string', // texto separado por ';'
        ]);

        $data['complements'] = $this->normalizeComplements($data['complements'] ?? null);

        $product->update($data);

        return back()->with('ok', __('global.product_updated'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        // Política: nunca hard delete
        if ($product->trashed()) {
            return back()->with('ok', __('global.product_already_archived'));
        }

        $product->delete(); // soft delete
        return back()->with('ok', __('global.product_archived'));
    }

    /**
     * Converte string "a; b; c" -> ['a','b','c']
     * Remove entradas vazias e trim em cada item. Retorna null se vazio.
     */
    private function normalizeComplements(?string $text): ?array
    {
        if ($text === null) return null;

        // Troca quebras de linha por ';' (evita itens quebrados), então explode
        $text = str_replace(["\r\n","\n","\r"], ';', $text);

        $parts = array_map('trim', explode(';', $text));
        // remove vazios
        $parts = array_values(array_filter($parts, fn($v) => $v !== ''));

        return count($parts) ? $parts : null;
    }
}
