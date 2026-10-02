<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Services\SeoManager;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(SeoManager $seo): View
    {
        return view('faqs.index', [
            'faqs' => Faq::active()->orderBy('sort_order')->orderBy('id')->get(),
            'metadata' => $seo->forListing('Pertanyaan umum', 'faqs.index'),
        ]);
    }
}
