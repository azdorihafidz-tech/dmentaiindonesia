<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\SeoManager;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(SeoManager $seo): View
    {
        return view('products.index', [
            'products' => Product::available()->with(['category', 'image'])
                ->orderBy('category_id')->orderBy('sort_order')->paginate(12),
            'metadata' => $seo->forListing('Menu', 'products.index'),
        ]);
    }

    public function show(string $slug, SeoManager $seo): View
    {
        $product = Product::available()->with(['category', 'image', 'ogMedia'])->where('slug', $slug)->firstOrFail();

        return view('products.show', [
            'product' => $product,
            'metadata' => $seo->forContent($product, 'products.show', 'name', 'short_description', 'description'),
        ]);
    }
}
