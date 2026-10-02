<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Media;
use App\Models\Outlet;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Promotion;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Explicitly executed, idempotent LOCAL DEMO content. This is intentionally
 * not called by DatabaseSeeder so production seeding never inserts demo data.
 */
class DemoContentSeeder extends Seeder
{
    /** @var array<string, Media> */
    private array $media = [];

    public function run(): void
    {
        $this->seedArtwork();

        DB::transaction(function (): void {
            $this->seedSettings();
            $categories = $this->seedProductCategories();
            $this->seedProducts($categories);
            $this->seedPromotions();
            $this->seedOutlets();
            $articleCategories = $this->seedArticleCategories();
            $this->seedArticles($articleCategories);
            $this->seedGallery();
            $this->seedTestimonials();
            $this->seedFaqs();
            $this->seedPages();
        });
    }

    private function seedArtwork(): void
    {
        $artworks = [
            'menu-dimsum' => 'Dimsum',
            'menu-mentai' => 'Dimsum Mentai',
            'menu-gyoza' => 'Gyoza',
            'menu-frozen' => 'Frozen Food',
            'menu-paket' => 'Paket Dimsum',
            'menu-minuman' => 'Minuman',
            'galeri-closeup' => 'Close-up Dimsum',
            'galeri-table' => 'Sajian di Meja',
            'galeri-sharing' => 'Paket untuk Berbagi',
            'galeri-kemasan' => 'Kemasan Demo',
        ];

        foreach ($artworks as $key => $label) {
            $path = 'demo-content/'.$key.'.svg';
            $svg = $this->svgArtwork($label);
            Storage::disk('public')->put($path, $svg);

            $this->media[$key] = Media::updateOrCreate(
                ['disk' => 'public', 'path' => $path],
                [
                    'original_name' => $key.'.svg',
                    'mime_type' => 'image/svg+xml',
                    'file_size' => strlen($svg),
                    'alt_text' => 'Ilustrasi '.$label.' — DEMO lokal D’Mentai Indonesia',
                    'caption' => 'Placeholder ilustrasi untuk pengujian lokal; bukan foto produk asli.',
                    'folder' => 'demo-content',
                ],
            );
        }
    }

    private function svgArtwork(string $label): string
    {
        $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_XML1, 'UTF-8');

        return <<<SVG
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 600" role="img" aria-labelledby="title desc">
              <title id="title">{$safeLabel} — ilustrasi demo</title>
              <desc id="desc">Placeholder ilustrasi makanan berwarna oranye, krem, dan hitam untuk pengujian lokal D’Mentai.</desc>
              <rect width="800" height="600" rx="38" fill="#fff4e8"/>
              <circle cx="675" cy="100" r="150" fill="#ffd9b8"/>
              <circle cx="104" cy="520" r="180" fill="#ffe7d1"/>
              <path d="M130 350c0-125 105-220 270-220s270 95 270 220H130Z" fill="#24201d"/>
              <path d="M151 349c10-91 101-160 249-160s239 69 249 160H151Z" fill="#f47721"/>
              <path d="M190 325c31-65 98-103 210-103s179 38 210 103" fill="none" stroke="#ffb36f" stroke-width="16" stroke-linecap="round"/>
              <g fill="#fff4e8" stroke="#d95316" stroke-width="7">
                <ellipse cx="275" cy="288" rx="42" ry="31"/><ellipse cx="400" cy="270" rx="44" ry="33"/><ellipse cx="525" cy="288" rx="42" ry="31"/>
              </g>
              <g fill="#f47721"><circle cx="275" cy="282" r="8"/><circle cx="400" cy="264" r="8"/><circle cx="525" cy="282" r="8"/></g>
              <path d="M122 350h556c-17 98-119 165-278 165s-261-67-278-165Z" fill="#24201d"/>
              <path d="M193 402c57 51 357 51 414 0" fill="none" stroke="#fff4e8" stroke-width="10" stroke-linecap="round" opacity=".85"/>
              <rect x="48" y="38" width="224" height="52" rx="26" fill="#24201d"/>
              <text x="160" y="72" fill="#fff" font-family="Arial,sans-serif" font-size="22" font-weight="700" text-anchor="middle" letter-spacing="3">VISUAL DEMO</text>
              <text x="400" y="566" fill="#24201d" font-family="Arial,sans-serif" font-size="30" font-weight="700" text-anchor="middle">{$safeLabel}</text>
            </svg>
            SVG;
    }

    private function seedSettings(): void
    {
        $settings = [
            'brand.site_name' => "D'Mentai Indonesia",
            'brand.description' => "D'Mentai Indonesia adalah brand kuliner yang menghadirkan aneka dimsum dengan sentuhan saus mentai yang creamy, gurih, dan modern. Nikmati pilihan dimsum untuk camilan, makan santai, maupun berbagi bersama keluarga dan teman.",
            'brand.tagline' => 'Rasa Creamy, Bikin Nagih.',
            'contact.email' => 'hello@dmentai-demo.local',
            'contact.phone' => '+62 800 0000 0000',
            'contact.whatsapp' => '+62 800 0000 0000',
            'location.address' => "D'Mentai Indonesia Demo Store — lokasi fiktif untuk pengujian lokal",
            'location.city' => 'Demo City',
            'location.province' => 'Demo Province',
            'location.country' => 'Indonesia',
            'location.business_hours' => 'DEMO / LOCAL TESTING: Senin - Minggu, 10:00 - 22:00',
            'footer.text' => 'DEMO / LOCAL TESTING — konten, harga, kontak, lokasi, dan materi visual di situs ini hanya contoh untuk pengujian lokal; bukan informasi bisnis terverifikasi.',
            'footer.copyright' => '© '.now()->year." D'Mentai Indonesia — materi DEMO lokal",
            'seo.default_title' => "D'Mentai Indonesia — Dimsum Mentai Favorit",
            'seo.meta_description' => "Nikmati aneka dimsum dengan saus mentai creamy dan gurih dari D'Mentai Indonesia. Lihat menu, promo, outlet, artikel, dan berbagai pilihan dimsum favorit.",
            'seo.og_title' => "D'Mentai Indonesia — Rasa Creamy, Bikin Nagih",
            'seo.og_description' => 'Aneka dimsum dengan sentuhan mentai yang creamy, gurih, dan menggugah selera.',
        ];

        foreach ($settings as $key => $value) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['group' => Str::before($key, '.'), 'value' => $value],
            );
        }
    }

    /** @return array<string, ProductCategory> */
    private function seedProductCategories(): array
    {
        $items = [
            ['dimsum', 'Dimsum', 'Pilihan dimsum favorit dengan tekstur lembut dan isian yang menggugah selera.', 'menu-dimsum'],
            ['dimsum-mentai', 'Dimsum Mentai', 'Aneka dimsum dengan saus mentai creamy sebagai pelengkap yang bikin nagih.', 'menu-mentai'],
            ['gyoza', 'Gyoza', 'Pilihan gyoza dengan kulit tipis dan isian gurih.', 'menu-gyoza'],
            ['paket-hemat', 'Paket Hemat', 'Pilihan paket praktis untuk menikmati beberapa menu favorit sekaligus.', 'menu-paket'],
            ['frozen-food', 'Frozen Food', 'Pilihan frozen food untuk dinikmati kapan saja di rumah.', 'menu-frozen'],
            ['minuman', 'Minuman', 'Minuman segar sebagai pendamping menu D’Mentai.', 'menu-minuman'],
        ];

        $result = [];
        foreach ($items as $order => [$slug, $name, $description, $image]) {
            $result[$slug] = ProductCategory::updateOrCreate(
                ['slug' => 'demo-'.$slug],
                ['name' => $name, 'description' => $description, 'image_media_id' => $this->media[$image]->id, 'status' => 'active', 'sort_order' => $order + 1],
            );
        }

        return $result;
    }

    /** @param array<string, ProductCategory> $categories */
    private function seedProducts(array $categories): void
    {
        $products = [
            ['dimsum-ayam', 'Dimsum Ayam', 'dimsum', 15000, true, 'menu-dimsum'],
            ['dimsum-ayam-jamur', 'Dimsum Ayam Jamur', 'dimsum', 17000, false, 'menu-dimsum'],
            ['dimsum-udang', 'Dimsum Udang', 'dimsum', 20000, true, 'menu-dimsum'],
            ['dimsum-mentai-original', 'Dimsum Mentai Original', 'dimsum-mentai', 22000, true, 'menu-mentai'],
            ['dimsum-mentai-spicy', 'Dimsum Mentai Spicy', 'dimsum-mentai', 23000, false, 'menu-mentai'],
            ['dimsum-mentai-cheese', 'Dimsum Mentai Cheese', 'dimsum-mentai', 25000, true, 'menu-mentai'],
            ['gyoza-original', 'Gyoza Original', 'gyoza', 18000, false, 'menu-gyoza'],
            ['gyoza-pedas', 'Gyoza Pedas', 'gyoza', 19000, false, 'menu-gyoza'],
            ['gyoza-mentai', 'Gyoza Mentai', 'gyoza', 23000, true, 'menu-gyoza'],
            ['siomay-ayam', 'Siomay Ayam', 'dimsum', 16000, false, 'menu-dimsum'],
            ['hakau-udang', 'Hakau Udang', 'dimsum', 22000, false, 'menu-dimsum'],
            ['lumpia-kulit-tahu', 'Lumpia Kulit Tahu', 'dimsum', 18000, false, 'menu-dimsum'],
            ['paket-dmentai-1', "Paket D'Mentai 1", 'paket-hemat', 35000, true, 'menu-paket'],
            ['paket-dmentai-2', "Paket D'Mentai 2", 'paket-hemat', 45000, false, 'menu-paket'],
            ['paket-sharing', 'Paket Sharing', 'paket-hemat', 65000, true, 'menu-paket'],
            ['frozen-dimsum-ayam', 'Frozen Dimsum Ayam', 'frozen-food', 35000, false, 'menu-frozen'],
            ['frozen-gyoza', 'Frozen Gyoza', 'frozen-food', 35000, false, 'menu-frozen'],
            ['es-teh-manis', 'Es Teh Manis', 'minuman', 8000, false, 'menu-minuman'],
        ];

        foreach ($products as $order => [$slug, $name, $category, $price, $featured, $image]) {
            $label = 'Data menu dan harga ini merupakan contoh DEMO untuk pengujian lokal. Informasi produk dan komposisi belum diverifikasi.';
            Product::updateOrCreate(
                ['slug' => 'demo-'.$slug],
                [
                    'category_id' => $categories[$category]->id,
                    'name' => $name,
                    'short_description' => 'Contoh menu untuk pengujian tampilan katalog lokal.',
                    'description' => '<p>'.$label.'</p>',
                    'image_media_id' => $this->media[$image]->id,
                    'price' => $price,
                    'promotional_price' => null,
                    'status' => 'active',
                    'featured' => $featured,
                    'sort_order' => $order + 1,
                    'seo_title' => $name." | D'Mentai Indonesia — DEMO",
                    'seo_description' => $name.' dengan harga contoh Rp '.number_format($price, 0, ',', '.').'. Materi demo untuk pengujian lokal.',
                ],
            );
        }
    }

    private function seedPromotions(): void
    {
        $items = [
            ['promo-dimsum-mentai-weekend', 'Demo Promo Dimsum Mentai Weekend', 'Nikmati pilihan dimsum mentai favorit untuk menemani akhir pekan. Informasi ini merupakan materi demo tanpa penawaran diskon nyata.', 'menu-mentai', now()->subDays(2), now()->addDays(20), true],
            ['promo-paket-sharing', 'Demo Paket Sharing', 'Contoh paket untuk menguji tampilan promosi dan hubungan ke menu. Detail paket bukan penawaran komersial.', 'menu-paket', now()->subDay(), now()->addDays(14), false],
            ['promo-mentai-lovers', 'Demo Mentai Lovers', 'Contoh jadwal promosi mendatang untuk pengujian fitur penjadwalan CMS.', 'menu-mentai', now()->addDays(14), now()->addDays(30), false],
            ['promo-combo-hemat', 'Demo Combo Hemat — Arsip Kedaluwarsa', 'Contoh promosi kedaluwarsa untuk memverifikasi bahwa konten lama tidak muncul sebagai promosi aktif.', 'menu-paket', now()->subDays(30), now()->subDays(10), false],
        ];

        foreach ($items as $order => [$slug, $title, $description, $image, $start, $end, $featured]) {
            Promotion::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'short_description' => 'Materi promosi DEMO untuk pengujian lokal; tidak menyatakan diskon atau penawaran nyata.',
                    'description' => '<p>'.e($description).'</p>',
                    'banner_media_id' => $this->media[$image]->id,
                    'cta_text' => 'Lihat menu demo',
                    'cta_url' => '/menu',
                    'start_at' => $start,
                    'end_at' => $end,
                    'status' => 'active',
                    'featured' => $featured,
                    'sort_order' => $order + 1,
                    'seo_title' => $title." | D'Mentai Indonesia",
                    'seo_description' => $description,
                ],
            );
        }
    }

    private function seedOutlets(): void
    {
        $items = [
            ['pusat', 'Jl. Contoh Kuliner No. 10'],
            ['mall', 'Jl. Contoh Mall No. 20'],
            ['foodcourt', 'Jl. Contoh Foodcourt No. 30'],
        ];

        foreach ($items as $order => [$key, $address]) {
            $name = "D'Mentai Demo — ".ucfirst($key);
            Outlet::updateOrCreate(
                ['slug' => 'demo-outlet-'.$key],
                [
                    'name' => $name,
                    'address' => $address.' (alamat fiktif untuk DEMO lokal)',
                    'city' => 'Demo City',
                    'province' => 'Demo Province',
                    'country' => 'Indonesia',
                    'phone' => null,
                    'whatsapp' => null,
                    'business_hours' => 'DEMO: 10:00–22:00 setiap hari',
                    'google_maps_url' => null,
                    'latitude' => null,
                    'longitude' => null,
                    'image_media_id' => $this->media['galeri-table']->id,
                    'description' => 'Lokasi outlet fiktif untuk pengujian tampilan lokal. Bukan alamat operasional D’Mentai.',
                    'status' => 'active',
                    'featured' => $order < 2,
                    'sort_order' => $order + 1,
                    'seo_title' => $name." | Outlet DEMO D'Mentai",
                    'seo_description' => 'Informasi outlet fiktif untuk pengujian lokal. Alamat ini bukan lokasi usaha sebenarnya.',
                ],
            );
        }
    }

    /** @return array<string, ArticleCategory> */
    private function seedArticleCategories(): array
    {
        $items = ['Dimsum', 'Mentai', 'Gyoza', 'Tips Sajian', "D'Mentai"];
        $result = [];

        foreach ($items as $order => $name) {
            $slug = Str::slug($name);
            $result[$slug] = ArticleCategory::updateOrCreate(
                ['slug' => 'demo-'.$slug],
                ['name' => $name, 'description' => 'Kategori artikel DEMO untuk pengujian website lokal.', 'status' => 'active', 'sort_order' => $order + 1],
            );
        }

        return $result;
    }

    /** @param array<string, ArticleCategory> $categories */
    private function seedArticles(array $categories): void
    {
        $items = [
            ['mengenal-dimsum', 'Mengenal Dimsum dan Berbagai Jenisnya', 'Artikel demo tentang ragam sajian yang sering dikelompokkan sebagai dimsum.', 'dimsum', true, [
                'Istilah dimsum sering digunakan untuk menyebut beragam sajian kecil yang dinikmati sebagai camilan atau bagian dari waktu makan. Bentuk dan cara penyajiannya dapat berbeda-beda.',
                'Di dalam katalog demo ini terdapat beberapa nama menu untuk membantu menguji kategori dan halaman produk. Nama tersebut tidak menjadi pernyataan tentang resep atau komposisi tertentu.',
                'Saat memilih menu sungguhan, periksa informasi bahan dan kebutuhan diet langsung kepada penyedia makanan. Informasi itu tidak tersedia dalam data demo ini.',
            ]],
            ['dimsum-kapan-saja', 'Kenapa Dimsum Cocok Dinikmati Kapan Saja?', 'Catatan editorial demo mengenai dimsum sebagai ide sajian untuk berbagai suasana.', 'dimsum', true, [
                'Porsi sajian yang ringkas membuat dimsum mudah dibagikan dan cocok hadir di meja makan dalam suasana santai. Pilihan waktu menikmatinya tentu bergantung pada kebiasaan masing-masing.',
                'Dalam pengujian lokal, artikel ini juga membantu melihat bagaimana kutipan, paragraf, dan tanggal publikasi ditampilkan pada halaman artikel.',
                'Menu yang tercantum pada website ini berstatus contoh. Ketersediaan produk nyata perlu dikonfirmasi langsung kepada pihak usaha.',
            ]],
            ['saus-mentai-creamy', 'Kenikmatan Saus Mentai yang Creamy', 'Artikel demo yang membahas karakter rasa mentai secara umum tanpa klaim resep khusus.', 'mentai', true, [
                'Saus mentai dikenal luas sebagai inspirasi rasa dengan karakter gurih dan creamy. Setiap penyedia dapat memiliki racikan dan bahan yang berbeda.',
                'Nama menu mentai pada katalog ini adalah contoh untuk memperlihatkan variasi produk dan metadata SEO. Tidak ada informasi resep atau komposisi spesifik yang diklaim.',
                'Jika memiliki alergi atau kebutuhan diet, tanyakan informasi bahan kepada penyedia sebelum memesan produk sungguhan.',
            ]],
            ['menikmati-dimsum-bersama', 'Tips Menikmati Dimsum Bersama Keluarga', 'Ide penyajian sederhana untuk suasana makan bersama, ditulis sebagai materi demo.', 'tips-sajian', true, [
                'Sajian bersama dapat terasa lebih tertata dengan pilihan menu yang disusun di tengah meja. Siapkan alat makan dan ruang agar setiap orang nyaman mengambil bagiannya.',
                'Untuk acara keluarga, tanyakan preferensi dan pantangan makanan setiap orang sebelum menentukan menu. Informasi komposisi pada produk demo ini belum tersedia.',
                'Artikel ini merupakan konten orisinal untuk pengujian tampilan dan tidak menyatakan rekomendasi keamanan pangan tertentu.',
            ]],
            ['ide-menu-acara-santai', 'Ide Menu Dimsum untuk Acara Santai', 'Contoh artikel tentang menyusun variasi sajian untuk pertemuan informal.', 'dimsum', true, [
                'Pertemuan santai biasanya lebih fleksibel dalam hal pilihan sajian. Menggabungkan beberapa jenis menu dapat memberi variasi tampilan di meja.',
                'Kategori Dimsum, Gyoza, dan Paket Hemat pada website ini membantu menguji navigasi katalog. Seluruh produk dan harganya merupakan contoh lokal.',
                'Untuk kebutuhan acara nyata, pastikan jumlah, ketersediaan, harga, dan komposisi kepada penyedia terkait.',
            ]],
            ['gyoza-dan-dimsum', 'Gyoza dan Dimsum: Apa Bedanya?', 'Gambaran umum perbedaan nama sajian tanpa klaim bahan produk tertentu.', 'gyoza', true, [
                'Gyoza dan dimsum merujuk pada ragam sajian dengan bentuk, tradisi, serta cara penyajian yang dapat berbeda. Istilah dan resepnya juga dapat bervariasi di tiap tempat.',
                'Pada katalog demo, keduanya ditempatkan di kategori terpisah agar pengunjung dapat mencoba filter menu dan melihat relasi kategori.',
                'Deskripsi di sini bersifat pengantar umum, bukan keterangan komposisi untuk produk tertentu.',
            ]],
            ['panduan-menu-favorit', 'Panduan Memilih Menu Dimsum Favorit', 'Artikel draft untuk memastikan konten yang belum terbit tetap tersembunyi.', 'tips-sajian', false, [
                'Artikel ini berstatus draft demo dan tidak ditampilkan pada halaman publik.',
                'Naskah ini digunakan untuk memeriksa perilaku publikasi CMS.',
                'Seluruh rekomendasi final perlu ditinjau sebelum publikasi bisnis nyata.',
            ]],
        ];

        foreach ($items as $order => [$slug, $title, $excerpt, $category, $published, $paragraphs]) {
            $content = '<h2>'.e($title).'</h2>'.implode('', array_map(fn (string $paragraph): string => '<p>'.e($paragraph).'</p>', $paragraphs));
            Article::updateOrCreate(
                ['slug' => 'demo-'.$slug],
                [
                    'category_id' => $categories[$category]->id,
                    'title' => $title,
                    'excerpt' => 'ARTIKEL DEMO — '.$excerpt,
                    'content' => $content,
                    'featured_media_id' => $this->media[$category === 'gyoza' ? 'menu-gyoza' : ($category === 'mentai' ? 'menu-mentai' : 'menu-dimsum')]->id,
                    'author_id' => null,
                    'status' => $published ? Article::STATUS_PUBLISHED : Article::STATUS_DRAFT,
                    'published_at' => $published ? now()->subMonths(6 - min($order, 5))->subDays($order + 1) : null,
                    'seo_title' => $title." | D'Mentai Indonesia",
                    'seo_description' => 'Artikel demo lokal: '.$excerpt,
                ],
            );
        }
    }

    private function seedGallery(): void
    {
        $items = [
            ['Demo — Dimsum Mentai', 'Close-up menu mentai', 'galeri-closeup'],
            ['Demo — Sajian Dimsum', 'Sajian dimsum di meja', 'galeri-table'],
            ['Demo — Gyoza', 'Ilustrasi menu gyoza', 'menu-gyoza'],
            ['Demo — Paket Sharing', 'Contoh paket untuk berbagi', 'galeri-sharing'],
            ['Demo — Menu Mentai', 'Ilustrasi menu dimsum mentai', 'menu-mentai'],
            ['Demo — Kemasan', 'Ilustrasi kemasan demo lokal', 'galeri-kemasan'],
            ['Demo — Frozen Food', 'Ilustrasi kategori frozen food', 'menu-frozen'],
            ['Demo — Dimsum Ayam', 'Ilustrasi menu dimsum', 'menu-dimsum'],
            ['Demo — Paket Menu', 'Ilustrasi paket menu demo', 'menu-paket'],
            ['Demo — Minuman', 'Ilustrasi menu minuman demo', 'menu-minuman'],
        ];

        foreach ($items as $order => [$title, $theme, $image]) {
            GalleryItem::updateOrCreate(
                ['title' => $title],
                [
                    'image_media_id' => $this->media[$image]->id,
                    'caption' => 'Placeholder visual DEMO lokal, bukan foto produk atau suasana outlet asli.',
                    'category' => 'Demo lokal',
                    'alt_text' => $theme.' — ilustrasi DEMO lokal D’Mentai Indonesia',
                    'status' => 'active',
                    'sort_order' => $order + 1,
                ],
            );
        }
    }

    private function seedTestimonials(): void
    {
        $contents = [
            'Contoh testimonial untuk pengujian tampilan website lokal.',
            'Contoh ulasan demo untuk melihat susunan kartu testimonial.',
            'Materi demo ini bukan ulasan pelanggan yang terverifikasi.',
            'Teks contoh untuk menguji keterbacaan pada perangkat berbeda.',
            'Data testimonial fiktif khusus untuk pengujian lokal.',
        ];

        foreach ($contents as $index => $content) {
            Testimonial::updateOrCreate(
                ['customer_name' => sprintf('Contoh Testimonial %02d', $index + 1)],
                ['content' => $content, 'image_media_id' => null, 'status' => 'active', 'featured' => $index < 3, 'sort_order' => $index + 1],
            );
        }
    }

    private function seedFaqs(): void
    {
        $items = [
            ['Apa itu D’Mentai Indonesia?', 'Ini adalah website DEMO lokal untuk menguji tampilan CMS dan situs publik. Kontennya bukan informasi bisnis terverifikasi.'],
            ['Apa saja menu yang tersedia?', 'Katalog menampilkan contoh menu dimsum, gyoza, paket, frozen food, dan minuman untuk pengujian.'],
            ['Apakah tersedia menu dimsum mentai?', 'Ada beberapa entri menu mentai demo untuk menguji daftar dan halaman detail produk.'],
            ['Apakah tersedia menu gyoza?', 'Kategori gyoza berisi contoh data lokal. Ketersediaan menu nyata belum dikonfirmasi.'],
            ['Apakah tersedia produk frozen?', 'Website menampilkan contoh produk frozen untuk pengujian katalog lokal.'],
            ['Bagaimana cara melihat menu?', 'Buka halaman Menu untuk menjelajahi daftar dan kategori produk demo.'],
            ['Bagaimana cara menemukan outlet?', 'Halaman Outlet menampilkan lokasi fiktif berlabel demo, bukan alamat operasional.'],
            ['Di mana informasi promo dapat dilihat?', 'Buka halaman Promo. Hanya jadwal yang sedang berlaku menurut waktu lokal ditampilkan sebagai aktif.'],
            ['Apakah D’Mentai memiliki media sosial?', 'Tautan media sosial sengaja dikosongkan; jangan menganggap website demo ini mewakili akun resmi.'],
            ['Bagaimana cara menghubungi D’Mentai?', 'Email dan nomor di website ini adalah data placeholder lokal, bukan kanal kontak bisnis.'],
        ];

        foreach ($items as $order => [$question, $answer]) {
            Faq::updateOrCreate(
                ['question' => $question],
                ['answer' => $answer, 'category' => 'Demo lokal', 'status' => 'active', 'sort_order' => $order + 1],
            );
        }
    }

    private function seedPages(): void
    {
        $pages = [
            ['tentang-kami', 'Tentang D’Mentai Indonesia', 'Halaman ini merupakan konten DEMO untuk pengujian website lokal.', '<p>D’Mentai Indonesia hadir dengan konsep kuliner yang menggabungkan dimsum favorit dengan karakter saus mentai yang creamy dan gurih.</p><p>Halaman ini merupakan konten DEMO untuk pengujian website lokal. Tidak ada riwayat, lokasi, atau capaian usaha nyata yang dinyatakan di sini.</p>'],
            ['kontak', 'Kontak Demo', 'Informasi kontak fiktif khusus pengujian lokal.', '<p><strong>DEMO / LOCAL TESTING</strong> — detail berikut adalah placeholder dan bukan informasi kontak bisnis terverifikasi.</p><p>Email: hello@dmentai-demo.local<br>Telepon/WhatsApp: +62 800 0000 0000</p><p>Lokasi contoh: D’Mentai Indonesia Demo Store, Demo City, Demo Province, Indonesia.</p>'],
            ['kebijakan-privasi', 'Kebijakan Privasi Demo', 'Kebijakan privasi umum untuk situs demo lokal.', '<p>Halaman kebijakan privasi ini disediakan sebagai contoh untuk pengujian website lokal, bukan pemberitahuan hukum untuk layanan bisnis yang beroperasi.</p><h2>Informasi yang dikirimkan</h2><p>Jangan mengirimkan data pribadi atau informasi rahasia melalui situs demo ini. Formulir produksi dan proses pemrosesan data belum didefinisikan oleh konten contoh ini.</p><h2>Cookies dan layanan pihak ketiga</h2><p>Konfigurasi demo ini tidak menyatakan bahwa analitik, iklan, atau integrasi pihak ketiga sedang aktif. Periksa konfigurasi aplikasi sebelum penggunaan nyata.</p><h2>Kontak</h2><p>Detail kontak di halaman ini adalah placeholder lokal dan tidak mewakili pengelola bisnis.</p>'],
            ['syarat-ketentuan', 'Syarat & Ketentuan Demo', 'Ketentuan contoh untuk pengujian tampilan website lokal.', '<p>Konten pada website ini merupakan DEMO untuk pengujian lokal. Produk, harga, promosi, outlet, testimonial, dan informasi lain tidak boleh dianggap sebagai penawaran atau informasi bisnis yang terverifikasi.</p><h2>Penggunaan materi demo</h2><p>Gunakan situs ini hanya untuk meninjau tampilan dan alur aplikasi lokal. Jangan mengirimkan informasi rahasia atau mengandalkan data placeholder untuk transaksi.</p><h2>Informasi produk dan lokasi</h2><p>Seluruh nama, deskripsi, harga, dan alamat pada katalog demo dibuat untuk tujuan pengujian dan dapat berbeda dari layanan nyata.</p>'],
        ];

        foreach ($pages as $order => [$slug, $title, $excerpt, $content]) {
            Page::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'excerpt' => 'DEMO lokal — '.$excerpt,
                    'content' => $content,
                    'status' => Page::STATUS_PUBLISHED,
                    'published_at' => now()->subDays(20 - $order),
                    'seo_title' => $title." | D'Mentai Indonesia DEMO",
                    'seo_description' => $excerpt,
                ],
            );
        }
    }
}
