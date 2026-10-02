<?php

namespace App\Http\Controllers;

use App\Models\GalleryItem;
use App\Services\SeoManager;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(SeoManager $seo): View
    {
        return view('gallery.index', [
            'items' => GalleryItem::active()->with('image')->orderBy('sort_order')->paginate(18),
            'metadata' => $seo->forListing('Galeri', 'gallery.index'),
        ]);
    }
}
