<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\SeoManager;
use App\Services\SiteSettings;
use App\Services\WhatsAppLink;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request, SeoManager $seo): View
    {
        $category = ProductCategory::active()->where('slug', $request->query('kategori'))->first();

        return view('products.index', [
            'products' => Product::available()->with(['category', 'image'])
                ->when($category, fn ($query) => $query->where('category_id', $category->id))
                ->orderByDesc('featured')->orderBy('category_id')->orderBy('sort_order')->paginate(12)->withQueryString(),
            'categories' => ProductCategory::active()->orderBy('sort_order')->get(['id', 'name', 'slug']),
            'selectedCategory' => $category,
            'metadata' => $seo->forListing('Menu', 'products.index', $category ? "Menu kategori {$category->name}." : null),
        ]);
    }

    public function show(string $slug, SeoManager $seo, SiteSettings $settings, WhatsAppLink $whatsappLink): View
    {
        $product = Product::available()->with(['category', 'image', 'ogMedia'])->where('slug', $slug)->firstOrFail();

        return view('products.show', [
            'product' => $product,
            'whatsappUrl' => $whatsappLink->url(
                $settings->get('contact.whatsapp'),
                "Halo D'Mentai, saya ingin memesan {$product->name}.",
            ),
            'metadata' => $seo->forContent($product, 'products.show', 'name', 'short_description', 'description'),
        ]);
    }
}
