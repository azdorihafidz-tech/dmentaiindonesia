<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Services\SeoManager;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(SeoManager $seo): View
    {
        return view('promotions.index', [
            'promotions' => Promotion::available()->with('banner')->orderByDesc('featured')->orderBy('sort_order')->paginate(12),
            'metadata' => $seo->forListing('Promosi', 'promotions.index'),
        ]);
    }

    public function show(string $slug, SeoManager $seo): View
    {
        $promotion = Promotion::available()->with(['banner', 'ogMedia'])->where('slug', $slug)->firstOrFail();

        return view('promotions.show', [
            'promotion' => $promotion,
            'metadata' => $seo->forContent($promotion, 'promotions.show', 'title', 'short_description', 'description'),
        ]);
    }
}
