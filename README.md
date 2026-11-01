# D'Mentai Indonesia

Website resmi D'Mentai - Dimsum & Gyoza, dibangun dengan Laravel 12, Blade, Livewire, Tailwind CSS, Vite, dan Filament 5.

## Persiapan lokal (XAMPP)

1. Salin `.env.example` menjadi `.env` bila belum tersedia.
2. Atur koneksi MariaDB lokal pada `.env` dan pastikan database `dmentai_indonesia` tersedia. Untuk URL XAMPP `http://localhost/dmentaiindonesia`, gunakan `APP_URL` yang sesuai; bila memakai `php artisan serve`, sesuaikan ke URL server tersebut.
3. Jalankan `composer install` dan `npm install`.
4. Jalankan `php artisan key:generate` bila `APP_KEY` masih kosong.
5. Jalankan `php artisan migrate`.
6. Jalankan `npm run dev` dan `php artisan serve` pada terminal terpisah bila memakai server development Laravel.

Untuk mengakses melalui Apache XAMPP pada `/dmentaiindonesia`, root `.htaccess` dan `index.php` mengarahkan request ke Laravel `public/` tanpa membuka source project. Pastikan modul `mod_rewrite` dan `.htaccess` aktif di Apache.

Panel admin tersedia di `/admin`. Akses hanya diberikan kepada akun dengan `is_admin=true` melalui pemeriksaan `canAccessPanel()`. Kolom `is_admin` bernilai false secara default dan dikeluarkan dari mass assignment; akun admin harus diprovisikan oleh operator tepercaya.

### Statistik negara pengunjung

Website dapat mencari negara pengunjung menggunakan database GeoLite2 Country lokal. Buat akun GeoLite2 gratis di MaxMind, unduh database MMDB, lalu tempatkan sebagai `storage/app/geoip/GeoLite2-Country.mmdb` atau atur `GEOIP_DB_PATH` ke lokasi file tersebut. Aplikasi tidak mengirim alamat IP ke layanan luar dan tidak menyimpan IP; hanya kode/nama negara yang ditambahkan pada catatan kunjungan. IP lokal, privat, atau yang tidak ditemukan di database akan tercatat tanpa negara. Jika server produksi berada di balik reverse proxy, isi `TRUSTED_PROXIES` dengan alamat IP proxy tepercaya agar Laravel menerima alamat IP pengunjung dengan benar; jangan isi wildcard pada server yang dapat diakses langsung.

GeoLite2 memerlukan atribusi MaxMind dan database perlu diperbarui berkala. Beranda menampilkan atribusi ketika data negara tersedia. Lihat [ketentuan GeoLite](https://www.maxmind.com/en/geolite/eula) dan panduan [memperbarui database](https://dev.maxmind.com/geoip/updating-databases/).

## Core CMS

- Kelola identitas brand, kontak, sosial, lokasi, footer, integrasi, dan SEO melalui **Pengaturan website** di Filament.
- Kelola gambar melalui **Media**. Upload dibatasi ke JPEG, PNG, WebP, dan GIF hingga 5 MB. Jalankan `php artisan storage:link` agar disk `public` dapat disajikan melalui web.
- Kelola halaman di **Halaman**. Hanya halaman berstatus terbit yang tersedia pada URL slug-nya; konten HTML dibatasi oleh sanitizer allowlist saat ditampilkan.
- Metadata SEO dikelola terpusat oleh `SeoManager` dan Blade component `<x-seo>`.
- Sitemap dinamis tersedia di `/sitemap.xml`, sedangkan `/robots.txt` mengecualikan panel admin dan merujuk sitemap berdasarkan `APP_URL`.

## Persiapan konfigurasi produksi (belum deploy)

`.env.example` berisi nilai awal untuk pengembangan lokal, bukan konfigurasi siap pakai untuk server. Sebelum deployment, siapkan `.env` khusus server dengan `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://dmentaiindonesia.com`, kredensial MariaDB yang disediakan untuk aplikasi, serta `FILESYSTEM_DISK=public`. Jangan menyalin `.env` lokal atau kredensial ke Git. Build aset dan tinjau migration sebelum deployment; tidak ada deployment yang dilakukan dalam Phase 5.
