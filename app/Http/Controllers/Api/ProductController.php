<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        return Product::query()->select('id','name','model','color','size','price')->orderBy('name')->get();
    }

    public function destroy(Product $product)
    {
        if ($product->trashed()) {
            return back()->with('ok', __('global.product_already_deleted'));
        }

        $product->delete();
        return back()->with('ok', __('global.product_deleted'));
    }
}
