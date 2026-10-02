<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Services\SeoManager;
use Illuminate\View\View;

class OutletController extends Controller
{
    public function index(SeoManager $seo): View
    {
        return view('outlets.index', [
            'outlets' => Outlet::active()->with('image')->orderByDesc('featured')->orderBy('sort_order')->paginate(12),
            'metadata' => $seo->forListing('Outlet', 'outlets.index'),
        ]);
    }

    public function show(string $slug, SeoManager $seo): View
    {
        $outlet = Outlet::active()->with(['image', 'ogMedia'])->where('slug', $slug)->firstOrFail();

        return view('outlets.show', [
            'outlet' => $outlet,
            'metadata' => $seo->forContent($outlet, 'outlets.show', 'name', 'address', 'description'),
        ]);
    }
}
