# Sistem Rekomendasi Game

Aplikasi Laravel untuk memberikan rekomendasi game berdasarkan preferensi pengguna. Data game dibaca dari `dataset/games.csv`, lalu dihitung menggunakan TF-IDF dan cosine similarity.

Cover game diproses dengan urutan berikut:

1. Memakai `image_url` dari CSV jika tersedia.
2. Mencari cover berdasarkan judul melalui API CheapShark.
3. Memakai poster SVG lokal jika API atau gambar tidak tersedia.

Hasil pencarian cover disimpan di cache selama 30 hari. Kegagalan koneksi API tidak menggagalkan halaman rekomendasi.

## Kebutuhan server

- PHP 8.3 atau lebih baru beserta ekstensi Laravel standar (`curl`, `mbstring`, `openssl`, `pdo`, dan `xml`).
- Composer 2.
- SQLite, MySQL, atau PostgreSQL.
- HTTPS dan CA certificate PHP yang valid.
- Akses keluar HTTPS menuju `www.cheapshark.com`.
- Document root domain diarahkan ke folder `public`, bukan root proyek.

Frontend halaman utama menggunakan CSS inline, sehingga Node.js tidak wajib untuk deployment halaman rekomendasi saat ini.

## Instalasi lokal

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Buka `http://127.0.0.1:8000`.

## Deployment production

Jalankan perintah berikut dari root proyek di server:

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan optimize
```

Jika `.env` dan `APP_KEY` sudah ada dari deployment sebelumnya, jangan menyalin `.env.example` atau membuat ulang key.

Konfigurasi minimum production:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-kamu.com

GAME_COVERS_ENABLED=true
GAME_COVERS_URL=https://www.cheapshark.com/api/1.0/games
GAME_COVERS_TIMEOUT=3
GAME_COVERS_USER_AGENT="SistemRekomendasiGame/1.0 (https://domain-kamu.com)"
GAME_COVERS_VERIFY_SSL=true
```

`GAME_COVERS_VERIFY_SSL` selalu dipaksa aktif oleh aplikasi ketika `APP_ENV=production`. Jangan menyalin nilai `false` dari konfigurasi Laragon lokal.

Sesuaikan juga `DB_*`, `CACHE_STORE`, dan `SESSION_DRIVER` dengan layanan database hosting. Konfigurasi bawaan memakai SQLite dan membutuhkan file database serta direktori `database` yang dapat ditulis.

Pada server Linux, web server harus dapat menulis ke direktori berikut:

```bash
chmod -R ug+rw storage bootstrap/cache
```

Gunakan kepemilikan user/group yang sesuai dengan konfigurasi web server; jangan memakai permission `777`.

## Apache dan Nginx

Untuk Apache, aktifkan `mod_rewrite` dan izinkan aturan `.htaccess` pada folder `public`.

Untuk Nginx, arahkan request yang tidak menemukan file ke `public/index.php` menggunakan pola `try_files $uri $uri/ /index.php?$query_string`.

## Pemeriksaan setelah deployment

```bash
php artisan about
php artisan test
```

Periksa endpoint berikut:

- `/up` harus mengembalikan status HTTP 200.
- `/` harus menampilkan halaman rekomendasi.
- `/game-cover/fallback.svg?title=Hades` harus mengembalikan SVG.

Jika cover asli tidak muncul, periksa akses HTTPS keluar dan konfigurasi CA certificate PHP. Poster lokal akan tetap ditampilkan selama masalah tersebut diperbaiki.
