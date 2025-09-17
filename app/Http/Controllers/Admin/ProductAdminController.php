<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductAdminController extends Controller
{
    public function index(Request $r)
    {
        $q = trim((string)$r->query('q', ''));
        $items = Product::query()
            ->when($q !== '', fn($w) =>
                $w->where('name', 'like', "%$q%")
                  ->orWhere('model','like',"%$q%")
                  ->orWhere('color','like',"%$q%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', compact('items','q'));
    }

    public function create()
    {
        return view('admin.products.form', ['product' => null]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name'  => 'required|string|max:120',
            'model' => 'nullable|string|max:80',
            'color' => 'nullable|string|max:40',
            'size'  => 'nullable|string|max:40',
            'price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
        Product::create($data);
        return redirect()->route('admin.products.index')->with('ok','Produto criado');
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', compact('product'));
    }

    public function update(Request $r, Product $product)
    {
        $data = $r->validate([
            'name'  => 'required|string|max:120',
            'model' => 'nullable|string|max:80',
            'color' => 'nullable|string|max:40',
            'size'  => 'nullable|string|max:40',
            'price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
        $product->update($data);
        return redirect()->route('admin.products.index')->with('ok','Produto atualizado');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('ok','Produto removido');
    }
}
