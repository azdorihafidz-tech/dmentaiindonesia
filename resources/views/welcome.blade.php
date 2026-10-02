<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f97316">
    <x-seo :metadata="$metadata" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-stone-950 antialiased">
    <main class="mx-auto flex min-h-screen max-w-6xl flex-col justify-center px-6 py-20">
        <p class="mb-5 text-sm font-bold uppercase tracking-[0.24em] text-orange-600">{{ $settings->get('brand.tagline', 'Dimsum & Gyoza') }}</p>
        <h1 class="max-w-3xl text-5xl font-black tracking-tight sm:text-7xl">Halo, ini {{ $settings->get('brand.site_name', "D'Mentai Indonesia") }}.</h1>
        <p class="mt-6 max-w-xl text-lg leading-8 text-stone-600">{{ $settings->get('brand.tagline', 'Website resmi D’Mentai Indonesia. Informasi menu dan outlet akan hadir di sini.') }}</p>
        <a class="mt-9 inline-flex w-fit rounded-full bg-orange-500 px-6 py-3 font-semibold text-white transition hover:bg-orange-600 focus:outline-none focus:ring-4 focus:ring-orange-200" href="{{ url('/admin') }}">Masuk admin</a>
    </main>
    @if ($testimonials->isNotEmpty())
        <section class="mx-auto max-w-6xl px-6 pb-12" aria-labelledby="testimonials-heading">
            <h2 id="testimonials-heading" class="text-2xl font-bold">Cerita pelanggan</h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($testimonials as $testimonial)
                    <figure class="rounded-2xl border border-stone-200 bg-white p-5">
                        @if ($testimonial->image)<img src="{{ $testimonial->image->url }}" alt="{{ $testimonial->image->alt_text ?: '' }}" class="mb-4 h-12 w-12 rounded-full object-cover" loading="lazy">@endif
                        <blockquote class="leading-7 text-stone-700">{{ $testimonial->content }}</blockquote>
                        <figcaption class="mt-4 font-bold">{{ $testimonial->customer_name }}</figcaption>
                    </figure>
                @endforeach
            </div>
        </section>
    @endif
    <footer class="border-t border-stone-200 px-6 py-8 text-center text-sm text-stone-500">
        <p>{{ $settings->get('footer.text', 'Dimsum & Gyoza untuk menemani harimu.') }}</p>
        <p class="mt-2">{{ $settings->get('footer.copyright', '© '.date('Y').' '.$settings->get('brand.site_name', "D'Mentai Indonesia")) }}</p>
    </footer>
</body>
</html>
