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
