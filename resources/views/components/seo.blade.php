@props(['metadata'])
<title>{{ $metadata->title }}</title>
@if ($metadata->description)<meta name="description" content="{{ $metadata->description }}">@endif
<link rel="canonical" href="{{ $metadata->canonical }}">
<meta name="robots" content="{{ $metadata->robots }}">
<meta property="og:type" content="{{ request()->routeIs('articles.show') ? 'article' : 'website' }}">
<meta property="og:site_name" content="{{ app(\App\Services\SiteSettings::class)->get('brand.site_name', config('app.name')) }}">
<meta property="og:title" content="{{ $metadata->ogTitle }}">
@if ($metadata->ogDescription)<meta property="og:description" content="{{ $metadata->ogDescription }}">@endif
<meta property="og:url" content="{{ $metadata->canonical }}">
@if ($metadata->ogImage)<meta property="og:image" content="{{ $metadata->ogImage }}">@endif
<meta name="twitter:card" content="{{ $metadata->ogImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:url" content="{{ $metadata->canonical }}">
<meta name="twitter:title" content="{{ $metadata->ogTitle }}">
@if ($metadata->ogDescription)<meta name="twitter:description" content="{{ $metadata->ogDescription }}">@endif
