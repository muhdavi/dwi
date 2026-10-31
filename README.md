# Data Wilayah Indonesia

Aplikasi berbasis Laravel 13 untuk menyimpan data wilayah Indonesia dalam struktur bertingkat: provinsi, kabupaten/kota, kecamatan, dan desa/kelurahan. Database schema dan data awal tersedia melalui migration dan seeder Laravel.

> **Cakupan data:** `DatabaseSeeder` mengisi seluruh 38 provinsi, 514 kabupaten/kota, 7.285 kecamatan, dan 83.762 desa/kelurahan melalui `WilayahSeeder`. Dataset dibundel di `database/data/wilayah.json`, bersumber dari [cahyadsn/wilayah](https://github.com/cahyadsn/wilayah/blob/master/db/wilayah.sql) (snapshot diperbarui 13 Februari 2026, lisensi MIT), sehingga seeding tidak memerlukan akses jaringan.
>
> **Fitur aplikasi:** landing page dengan grafik statistik (`/`), REST API publik untuk seluruh tingkat wilayah (`/api/wilayah/...`, lihat [Endpoint API](#endpoint-api)), serta panel admin bertema [AdminLTE](https://adminlte.io) untuk CRUD data wilayah (lihat [Panel admin](#panel-admin)). Login/profil dari Laravel Breeze (registrasi dinonaktifkan; akun dibuat lewat seeder).

## Teknologi

- PHP `^8.3`
- Laravel `^13.0`
- MySQL (konfigurasi default di `.env.example`; Laravel juga menyediakan konfigurasi SQLite, PostgreSQL, dan SQL Server)
- AdminLTE 3 (Bootstrap 4, jQuery) untuk panel admin; Tailwind CSS untuk landing page dan halaman login
- Node.js dan npm untuk membangun aset frontend melalui Vite

## Menjalankan secara lokal

Pastikan PHP beserta ekstensi Laravel/PDO yang diperlukan, Composer, Node.js/npm, dan server database sudah tersedia.

1. Clone repository lalu pasang dependensi:

   ```bash
   git clone https://github.com/muhdavi/data-wilayah.git
   cd data-wilayah
   composer install
   npm install
   ```

2. Siapkan konfigurasi aplikasi dan database:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Buat database MySQL kosong, lalu sesuaikan `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` di `.env`. Atur juga `APP_NAME` dan `APP_URL` sesuai lingkungan.

3. Buat tabel dan isi data awal:

   ```bash
   php artisan migrate --seed
   ```

   Registrasi dinonaktifkan. `UserSeeder` membuat akun siap pakai (email sudah terverifikasi) dengan password dari `SEED_USER_PASSWORD` di `.env` (default `password`; **ganti di production**):

   | Email | Nama |
   | --- | --- |
   | `admin@datawilayah.test` | Administrator |
   | `davi@datawilayah.test` | Davi |
   | `operator@datawilayah.test` | Operator |

   Seeder wilayah memakai `insert` biasa, bukan operasi upsert. Jalankan pada database kosong; menjalankan seeder lagi dapat menimbulkan data duplikat atau error constraint.

4. Bangun aset dan jalankan server development:

   ```bash
   npm run build
   php artisan serve
   ```

   Buka URL yang ditampilkan Artisan, biasanya `http://127.0.0.1:8000`. Untuk pengembangan frontend dengan hot reload, jalankan `npm run dev` di terminal terpisah.

## Menggunakan data wilayah

Data disimpan dalam tabel berikut:

| Tabel | Kolom utama |
| --- | --- |
| `provinsis` | `id`, `provinsi` |
| `kabupatens` | `id`, `provinsi_id`, `kabupaten` |
| `kecamatans` | `id`, `kabupaten_id`, `kecamatan` |
| `kelurahans` | `id`, `kecamatan_id`, `desa` |

Relasi antartingkat menggunakan kolom foreign key. Data dapat dibaca lewat [API](#endpoint-api), query database, atau Eloquent. Contoh melalui Tinker:

```bash
php artisan tinker
```

```php
DB::table('provinsis')->orderBy('id')->get();
DB::table('kabupatens')->where('provinsi_id', '11')->get();
```

Nilai `id` menggunakan kode wilayah tanpa pemisah titik dari dataset, sehingga foreign key menghubungkan setiap tingkat sesuai kode induknya.

## Endpoint API

API bersifat publik, read-only, tanpa autentikasi, mengizinkan CORS, dan dibatasi 60 request/menit per IP. Base URL: `http://127.0.0.1:8000/api/wilayah` (ganti dengan domain Anda).

| Method | Endpoint | Keterangan |
| --- | --- | --- |
| GET | `/provinsi` | Daftar provinsi |
| GET | `/provinsi/{id}` | Detail provinsi |
| GET | `/provinsi/{id}/kabupaten` | Kabupaten/kota dalam provinsi |
| GET | `/kabupaten/{id}` | Detail kabupaten/kota |
| GET | `/kabupaten/{id}/kecamatan` | Kecamatan dalam kabupaten/kota |
| GET | `/kecamatan/{id}` | Detail kecamatan |
| GET | `/kecamatan/{id}/kelurahan` | Desa/kelurahan dalam kecamatan |
| GET | `/kelurahan/{id}` | Detail desa/kelurahan |

`{id}` adalah kode wilayah tanpa titik: `11`, `1101`, `110101`, `1101012001`.

Query opsional pada endpoint daftar:

- `q`: cari berdasarkan nama (mis. `?q=bandung`).
- `per_page` (1–500) dan `page`: aktifkan paginasi. Tanpa keduanya, seluruh data dikembalikan.

Contoh respons `GET /api/wilayah/provinsi/11/kabupaten?per_page=2`:

```json
{
  "data": [
    {"id": 1101, "kode": "11.01", "nama": "KAB. ACEH SELATAN", "provinsi_id": 11},
    {"id": 1102, "kode": "11.02", "nama": "KAB. ACEH TENGGARA", "provinsi_id": 11}
  ],
  "meta": {"current_page": 1, "per_page": 2, "total": 23, "last_page": 12}
}
```

Tanpa paginasi, `meta` hanya berisi `total`. Endpoint detail mengembalikan `{"data": {...}}`. Status: `200` sukses, `404` id tidak ditemukan, `422` parameter tidak valid, `429` melebihi batas request.

### Contoh penggunaan

```bash
curl "http://127.0.0.1:8000/api/wilayah/provinsi"
curl "http://127.0.0.1:8000/api/wilayah/kabupaten/1101/kecamatan?q=bakongan"
```

JavaScript (dropdown berantai provinsi → kabupaten → kecamatan → desa):

```js
const BASE = 'http://127.0.0.1:8000/api/wilayah';
const get = (path) => fetch(BASE + path).then((r) => r.json()).then((j) => j.data);

const provinsi = await get('/provinsi');
const kabupaten = await get(`/provinsi/${provinsi[0].id}/kabupaten`);
const kecamatan = await get(`/kabupaten/${kabupaten[0].id}/kecamatan`);
const desa = await get(`/kecamatan/${kecamatan[0].id}/kelurahan`);
```

PHP / Laravel:

```php
use Illuminate\Support\Facades\Http;

$kabupaten = Http::get('http://127.0.0.1:8000/api/wilayah/provinsi/11/kabupaten')->json('data');
```

## Panel admin

Setelah login (akun dari seeder), buka `/dashboard`. Menu sidebar menyediakan CRUD untuk **Provinsi**, **Kabupaten/Kota**, **Kecamatan**, dan **Desa/Kelurahan**:

- Daftar dengan pencarian (nama/kode) dan paginasi; klik jumlah data turunan untuk menelusuri ke tingkat di bawahnya (mis. kabupaten dalam sebuah provinsi).
- Tambah data: pilih induk lewat dropdown berantai, lalu isi kode (2 digit; 4 digit untuk desa/kelurahan) dan nama. Kode lengkap = kode induk + kode tersebut.
- Ubah data: hanya nama yang dapat diubah, karena kode menjadi acuan data turunan.
- Hapus data: ditolak bila masih memiliki data turunan.

Setiap perubahan otomatis menyegarkan statistik landing page. Setelah mengubah aset admin (`resources/css/admin.css`, `resources/js/admin.js`), jalankan `npm run build`.

## Deploy ke server

Deploy aplikasi Laravel ke server yang menyediakan PHP sesuai versi proyek, Composer, Node.js/npm saat proses build, serta database yang dikonfigurasi. Contoh langkah umum:

1. Ambil kode di server dan pasang dependensi production serta aset:

   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci
   npm run build
   ```

2. Buat `.env` production dengan kredensial database dan URL aplikasi yang benar. Pastikan:

   ```dotenv
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://domain-anda
   ```

   Isi `APP_KEY` dengan key aplikasi yang aman (hasilkan satu kali menggunakan `php artisan key:generate` pada setup awal). Jangan commit atau membagikan `.env`.

3. Jalankan migration dan, bila database masih kosong, data awal:

   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```

   Seeder hanya jalankan pada database yang memang perlu diisi data awal. Periksa cakupan dan isi seed sebelum menjalankannya di production.

4. Siapkan storage, cache konfigurasi, dan permission:

   ```bash
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

   Pastikan proses web server dapat menulis ke direktori `storage` dan `bootstrap/cache`. Arahkan document root virtual host ke direktori `public/`, bukan root repository, dan gunakan konfigurasi HTTPS untuk domain production.

## Pengujian

Jalankan test suite (memakai SQLite in-memory, sehingga ekstensi `pdo_sqlite` diperlukan) dengan:

```bash
./vendor/bin/pest
```

## Lisensi

Repository ini menggunakan [MIT License](LICENCE).
