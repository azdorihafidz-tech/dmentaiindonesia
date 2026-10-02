<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Services\PageContentSanitizer;
use App\Services\SeoManager;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request, SeoManager $seo): View
    {
        $selectedCategory = $request->query('kategori');
        $category = $selectedCategory
            ? ArticleCategory::active()->where('slug', $selectedCategory)->first()
            : null;
        $articles = Article::published()->with(['category', 'featuredMedia', 'author'])
            ->when($category, fn ($query) => $query->where('category_id', $category->id))
            ->orderByDesc('published_at')->paginate(10)->withQueryString();

        return view('articles.index', [
            'articles' => $articles,
            'categories' => ArticleCategory::active()->orderBy('sort_order')->get(['id', 'name', 'slug']),
            'selectedCategory' => $category,
            'metadata' => $seo->forListing('Artikel', 'articles.index', $category ? "Artikel kategori {$category->name}." : null),
        ]);
    }

    public function show(string $slug, SeoManager $seo, PageContentSanitizer $sanitizer): View
    {
        $article = Article::published()->with(['category', 'featuredMedia', 'author', 'ogMedia'])
            ->where('slug', $slug)->firstOrFail();

        return view('articles.show', [
            'article' => $article,
            'content' => $sanitizer->sanitize($article->content),
            'metadata' => $seo->forContent($article, 'articles.show', 'title', 'excerpt', 'content'),
        ]);
    }
}
