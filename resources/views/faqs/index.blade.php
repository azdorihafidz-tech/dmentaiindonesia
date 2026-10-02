<x-layouts.public :metadata="$metadata">
    <section class="page-intro"><p class="eyebrow">Bantuan</p><h1>Pertanyaan umum</h1><p>Jawaban untuk hal yang sering ditanyakan.</p></section>
    @if ($faqs->isEmpty())
        <p class="empty-state">Belum ada pertanyaan yang ditampilkan.</p>
    @else
        <div class="mx-auto max-w-3xl space-y-8">
            @foreach ($faqs->groupBy(fn ($faq) => $faq->category ?: 'Pertanyaan umum') as $category => $items)
                <section aria-label="{{ $category }}">
                    @if ($faqs->pluck('category')->filter()->unique()->count() > 1)<h2 class="mb-3 text-lg font-bold">{{ $category }}</h2>@endif
                    <div class="faq-stack">
                        @foreach ($items as $faq)
                            <details class="faq-item">
                                <summary>
                                    {{ $faq->question }}
                                    <span aria-hidden="true">+</span>
                                </summary>
                                <p class="mt-4 whitespace-pre-line leading-7 text-stone-600">{{ $faq->answer }}</p>
                            </details>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @endif
</x-layouts.public>
