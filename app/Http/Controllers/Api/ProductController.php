<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Services\ProductPhotoService;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private ProductPhotoService $svc) {}

    public function index()
    {
        return Product::orderBy('name')->get();
    }

    public function destroy(Product $product)
    {
        if ($product->trashed()) {
            return back()->with('ok', __('global.product_already_deleted'));
        }

        $product->delete();
        return back()->with('ok', __('global.product_deleted'));
    }
    
    public function storePhotos(Request $request, Product $product)
    {
        $request->validate([
            'photo' => ['required','file','image','mimes:jpeg,jpg,png','max:8192'],
        ]);

        $updated = $this->svc->addPhoto($product, $request->file('photo'));
        return response()->json($updated);
    }

    public function destroyPhotos(Request $request, Product $product)
    {
        $request->validate([
            'path' => ['required','string'],
        ]);

        $updated = $this->svc->removePhoto($product, $request->string('path')->toString());
        return response()->json($updated);
    }
}
