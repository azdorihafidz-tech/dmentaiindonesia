# D'Mentai Indonesia

Website resmi D'Mentai - Dimsum & Gyoza, dibangun dengan Laravel 12, Blade, Livewire, Tailwind CSS, Vite, dan Filament 5.

## Persiapan lokal (XAMPP)

1. Salin `.env.example` menjadi `.env` bila belum tersedia.
2. Atur koneksi MariaDB lokal pada `.env` dan pastikan database `dmentai_indonesia` tersedia.
3. Jalankan `composer install` dan `npm install`.
4. Jalankan `php artisan key:generate` bila `APP_KEY` masih kosong.
5. Jalankan `php artisan migrate`.
6. Jalankan `npm run dev` dan `php artisan serve`.

Panel admin tersedia di `/admin`. Akses hanya diberikan kepada akun dengan `is_admin=true` melalui pemeriksaan `canAccessPanel()`. Kolom `is_admin` bernilai false secara default dan dikeluarkan dari mass assignment; akun admin harus diprovisikan oleh operator tepercaya.

## Core CMS

- Kelola identitas brand, kontak, sosial, lokasi, footer, integrasi, dan SEO melalui **Pengaturan website** di Filament.
- Kelola gambar melalui **Media**. Upload dibatasi ke JPEG, PNG, WebP, dan GIF hingga 5 MB. Jalankan `php artisan storage:link` agar disk `public` dapat disajikan melalui web.
- Kelola halaman di **Halaman**. Hanya halaman berstatus terbit yang tersedia pada URL slug-nya; konten HTML dibatasi oleh sanitizer allowlist saat ditampilkan.
- Metadata SEO dikelola terpusat oleh `SeoManager` dan Blade component `<x-seo>`.
- Sitemap dinamis tersedia di `/sitemap.xml`, sedangkan `/robots.txt` mengecualikan panel admin dan merujuk sitemap berdasarkan `APP_URL`.
