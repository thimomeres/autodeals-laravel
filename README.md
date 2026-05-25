# AutoDeals — Dashboard Control Center & Showroom ERP

Backoffice internal siap produksi untuk mengelola inventori showroom, meninjau penawaran pembeli, dan melacak penjualan yang sudah ditutup beserta perhitungan profit otomatis.

---

## 1. Ringkasan Proyek & Tech Stack

| Item | Keterangan |
|------|------------|
| **Proyek** | AutoDeals — Dashboard Control Center & Showroom ERP |
| **Backend** | Laravel 13 (`^13.8`) |
| **Runtime** | PHP 8.4 |
| **Frontend (Admin UI)** | Tailwind CSS v4 (CDN), Alpine.js, Lucide Icons, SweetAlert2 |
| **Database** | MySQL 8.4 |
| **Containerization** | Docker Compose (aplikasi PHP + MySQL) |

### Fitur Utama

- **Dashboard Overview Real-time** — Nilai inventori, sebaran stok, dan notifikasi penawaran yang masih pending.
- **Pelacakan Inventori Kendaraan Otomatis** — CRUD, unggah gambar, dan alur status (`available` → `pending` → `sold`).
- **Modal Tinjauan Penawaran Dinamis** — Mekanisme terima / tolak dengan SweetAlert2; menerima penawaran menandai kendaraan sebagai terjual dan menolak penawaran pending lain yang bersaing.
- **Buku Besar Penjualan & Margin Profit Otomatis** — Riwayat penjualan diambil dari `offers` dengan `status = accepted`; profit = `price_offered` − `cars.price`.

---

## 2. Prasyarat

Pasang perangkat lunak berikut sebelum memulai:

| Alat | Fungsi |
|------|--------|
| [Docker Desktop](https://www.docker.com/products/docker-desktop/) | Menjalankan aplikasi dan MySQL lewat Compose |
| [Git](https://git-scm.com/) | Clone atau version control repositori |
| [Postman](https://www.postman.com/) (atau sejenisnya) | Menguji API pengiriman penawaran publik |

Opsional (pengembangan lokal tanpa Docker): PHP 8.4+, Composer 2.x, MySQL, dan Node.js jika Anda memperluas pipeline aset.

---

## 3. Struktur Proyek (Ringkas)

```text
autodeals-showroom/
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php      # Login / logout
│   │   └── CarController.php       # Inventori, penawaran, penjualan, dashboard
│   └── Models/
│       ├── Car.php
│       ├── Offer.php
│       └── CarImage.php
├── database/
│   ├── migrations/                 # cars, offers, users, dll.
│   └── seeders/DatabaseSeeder.php  # Akun admin default
├── docker/
│   └── entrypoint.sh
├── public/                         # CSS, JS, aset unggahan (via storage link)
├── resources/views/                # Blade UI admin (dashboard, inventori, sales, …)
├── routes/web.php
├── Dockerfile
├── docker-compose.yml
├── .env.example
└── README.md
```

---

## 4. Setup Docker & Cara Menjalankan

### Langkah 1 — Clone atau ekstrak repositori

```bash
git clone <url-repositori-anda> autodeals-showroom
cd autodeals-showroom
```

### Langkah 2 — Buat dan konfigurasi file environment

```bash
cp .env.example .env
```

Edit `.env` untuk Docker. **Atur `DB_HOST` ke nama service MySQL** (`mysql`), bukan `127.0.0.1`, dan set **`OFFER_API_KEY`** untuk API penawaran (lihat [bagian Keamanan](#9-keamanan-security)):

```env
APP_NAME=AutoDeals
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=db_autodeals
DB_USERNAME=autodeals
DB_PASSWORD=secret
```

> Nilai-nilai ini sesuai dengan `docker-compose.yml`. Container MySQL membuka port `3306` di host untuk alat eksternal jika diperlukan.

### Langkah 3 — Build dan jalankan container

```bash
docker compose up -d --build
```

Tunggu hingga service `mysql` dalam status healthy (Compose mengaturnya lewat `depends_on`).

| Service | Nama container | Akses dari host |
|---------|----------------|-----------------|
| `app` | `autodeals-app` | http://localhost:8000 |
| `mysql` | `autodeals-mysql` | `127.0.0.1:3306` |

### Langkah 4 — Instal dependensi PHP di dalam container app

```bash
docker compose exec app composer install
```

### Langkah 5 — Generate application key

```bash
docker compose exec app php artisan key:generate
```

### Langkah 6 — Migrasi dan seed database

```bash
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan storage:link
```

Perintah ini membuat semua tabel dan akun admin default:

| Nama | Email | Password |
|------|-------|----------|
| Timo | `timo@gmail.com` | `password123` |
| Mima | `mima@gmail.com` | `password123` |

> `migrate:fresh` **menghapus semua data**. Gunakan `php artisan migrate --seed` pada environment yang sudah berjalan jika Anda hanya perlu menerapkan migrasi baru.

### Langkah 7 — Buka aplikasi

1. Kunjungi **http://localhost:8000**
2. Login dengan `timo@gmail.com` / `password123`
3. Tambahkan minimal satu kendaraan dari **Inventory → Add Vehicle** (wajib sebelum uji penawaran via Postman)

### Perintah Docker yang berguna

```bash
# Lihat log
docker compose logs -f app

# Hentikan stack
docker compose down

# Hentikan dan hapus volume database
docker compose down -v
```

---

## 5. Pengujian API dengan Postman (Simulasi Penawaran)

Endpoint penerimaan penawaran bersifat **publik** (tanpa login) dan dikecualikan dari verifikasi CSRF.

| Field | Nilai |
|-------|-------|
| **Method** | `POST` |
| **URL** | `http://localhost:8000/api/submit-offer` |
| **Headers** | `Content-Type: application/json`, `Accept: application/json`, `X-API-Key: <OFFER_API_KEY dari .env>` |

### Contoh body JSON

```json
{
  "car_id": 1,
  "buyer_name": "Budi Santoso",
  "price_offered": 485000000
}
```

### Aturan validasi

| Field | Aturan |
|-------|--------|
| `car_id` | Wajib; harus ada di `cars.id` |
| `buyer_name` | String wajib (maks. 255 karakter) |
| `price_offered` | Numerik wajib ≥ 0 |

### Perilaku yang diharapkan

1. Baris baru dimasukkan ke `offers` dengan `status = pending_review`.
2. Status kendaraan terkait berubah menjadi `pending` (kecuali sudah `sold`).
3. Penawaran muncul di **Dashboard** untuk ditinjau admin (Terima / Tolak).

### Pengingat margin profit

Profit penjualan dihitung sebagai:

```text
Net Profit = price_offered − cars.price (harga pokok)
```

Saat pengujian, gunakan **`price_offered` sama dengan atau di atas harga pokok kendaraan** kecuali Anda sengaja ingin mendemokan margin negatif di buku besar Sales. Penawaran terlalu rendah akan tampil merah (rugi) di **Sales History**.

### Contoh cURL

```bash
curl -X POST http://localhost:8000/api/submit-offer \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-API-Key: change-me-to-a-long-random-secret" \
  -d '{"car_id":1,"buyer_name":"Budi Santoso","price_offered":485000000}'
```

---

## 6. Ringkasan Skema Database

### Relasi inti

```text
users (admin)
  └── cars (aset inventori)
        └── offers (penawaran pembeli)
```

- **`offers.car_id`** → foreign key ke **`cars.id`** (`ON DELETE CASCADE`).
- Setiap penawaran menyimpan `buyer_name`, `price_offered`, dan `status`.

### Siklus status penawaran

| Status | Arti |
|--------|------|
| `pending_review` | Penawaran baru menunggu keputusan admin |
| `accepted` | Deal ditutup — muncul di Sales History |
| `rejected` | Ditolak admin atau otomatis ditolak saat penawaran lain menang |

### Alur penerimaan (otomatis)

Saat admin **menerima** penawaran (`PATCH /offers/{id}/accept`):

1. `status` penawaran tersebut → **`accepted`**
2. **`cars.status`** terkait → **`sold`**
3. Semua penawaran **`pending_review`** lain untuk `car_id` yang sama → **`rejected`**

Saat admin **menolak** penawaran dan tidak ada penawaran pending tersisa untuk mobil itu, kendaraan kembali ke **`available`**.

### Sumber data buku besar penjualan

Halaman **Sales** (`GET /sales`) hanya membaca:

```sql
SELECT * FROM offers WHERE status = 'accepted' ORDER BY updated_at DESC;
```

Total revenue dan profit diagregasi di `CarController@sales` menggunakan eager loading `Offer::with('car')`.

---

## 7. Fitur Tambahan (Efektivitas Operasional)

| Fitur | Keterangan |
|-------|------------|
| **Role Owner / Staff** | Timo = Owner (hapus mobil, kelola admin, audit). Mima = Staff (CRUD tanpa hapus). |
| **Kelola Admin** | `/admin/users` — tambah/hapus akun (Owner saja) |
| **Audit Trail** | `/admin/activity` — log login, penawaran, perubahan status, CRUD mobil |
| **Profil & Password** | `/profile` — ganti password (semua admin) |
| **Dashboard riil** | Target penjualan bulanan dari `MONTHLY_SALES_TARGET`; unit sold = offer accepted bulan ini |
| **Export CSV Sales** | Tombol di halaman Sales → unduh laporan |
| **Riwayat penawaran** | Detail mobil memakai tabel `offers` (bukan `inquiries`) |
| **Redirect `/inventory`** | Alias ke `/infentory` |

Setelah pull/update, jalankan:

```bash
php artisan migrate
```

---

## 8. Referensi Rute Admin

| Method | URI | Name | Auth |
|--------|-----|------|------|
| GET | `/` | `login` | Publik |
| POST | `/login` | `login.post` | Publik |
| GET | `/dashboard` | `dashboard` | Ya |
| GET | `/infentory` | `inventory` | Ya |
| GET | `/sales` | `sales` | Ya |
| GET | `/sales/export` | `sales.export` | Ya |
| GET | `/profile` | `profile.edit` | Ya |
| GET | `/admin/users` | `users.index` | Owner |
| GET | `/admin/activity` | `activity.index` | Owner |
| PATCH | `/offers/{id}/accept` | `offers.accept` | Ya |
| DELETE | `/vehicle/{id}` | `Car.destroy` | Owner |
| PATCH | `/offers/{id}/reject` | `offers.reject` | Ya |
| POST | `/api/submit-offer` | — | Publik (API Key + rate limit) |

---

## 9. Troubleshooting

| Masalah | Solusi |
|---------|--------|
| `SQLSTATE[01000] Data truncated for column 'status'` saat accept | Jalankan migrasi: `php artisan migrate` (termasuk perbaikan enum untuk `accepted`) |
| Postman mengembalikan 419 | Gunakan `/api/submit-offer` dengan JSON; rute ini bebas CSRF |
| Validasi `car_id` gagal | Buat kendaraan dulu lewat UI admin; gunakan `id` numeriknya |
| Gambar unggahan 404 | Jalankan `php artisan storage:link` di dalam container |
| Koneksi DB ditolak dari container app | Pastikan `DB_HOST=mysql` di `.env`, bukan `127.0.0.1` |
| API mengembalikan 401 Unauthorized | Tambahkan header `X-API-Key` yang sama dengan `OFFER_API_KEY` di `.env` |
| API mengembalikan 503 | `OFFER_API_KEY` belum diisi di `.env` |
| API mengembalikan 429 Too Many Requests | Tunggu 1 menit; batas submit-offer adalah 30 request/menit per IP |

---

## 10. Keamanan (Security)

Bagian ini merangkum perlindungan yang **sudah diterapkan** di proyek dan rekomendasi untuk lingkungan production.

### Sudah diterapkan di codebase

| Lapisan | Implementasi | File / lokasi |
|---------|----------------|---------------|
| **Autentikasi admin** | Semua rute dashboard, inventori, sales, dan accept/reject offer dilindungi middleware `auth` | `routes/web.php` |
| **Redirect tamu** | Mengetik `/dashboard` atau `/infentory` tanpa login → otomatis ke halaman login | `bootstrap/app.php` → `redirectGuestsTo` |
| **Redirect user login** | Admin sudah login yang buka `/login` → dialihkan ke dashboard | `bootstrap/app.php` → `redirectUsersTo` + middleware `guest` |
| **Logout terproteksi** | `POST /logout` hanya untuk user yang sedang login | `routes/web.php` |
| **API Key endpoint penawaran** | `POST /api/submit-offer` wajib header `X-API-Key` yang cocok dengan `OFFER_API_KEY` (perbandingan aman via `hash_equals`) | `app/Http/Middleware/VerifyOfferApiKey.php` |
| **Rate limiting login** | Maks. 5 percobaan login per menit per IP | `routes/web.php` → `throttle:5,1` |
| **Rate limiting API penawaran** | Maks. 30 request per menit per IP | `routes/web.php` → `throttle:30,1` |
| **Validasi bisnis** | Penawaran ditolak jika mobil berstatus `sold`; accept/reject hanya untuk `pending_review` | `CarController.php` |
| **Validasi input** | `buyer_name` dibatasi karakter aman; `price_offered` punya batas min/max numerik | `CarController::submitOffer()` |
| **Respons API minimal** | JSON sukses tidak lagi mengekspos seluruh model Eloquent / relasi internal | `CarController::submitOffer()` |
| **CSRF form admin** | Form web admin tetap memakai CSRF; hanya API JSON yang dikecualikan | `bootstrap/app.php` |
| **Regenerasi session** | Setelah login berhasil, session di-regenerate | `AuthController::login()` |

### Konfigurasi wajib di `.env`

```env
# Ganti dengan string acak panjang (min. 32 karakter). Jangan commit nilai production ke Git.
OFFER_API_KEY=ubah-dengan-secret-acak-panjang

APP_DEBUG=false          # production: selalu false
APP_ENV=production     # production
```

Setelah mengubah `.env`:

```bash
php artisan config:clear
```

### Postman — header keamanan API

Selain `Content-Type` dan `Accept`, tambahkan:

| Header | Nilai |
|--------|--------|
| `X-API-Key` | Sama persis dengan `OFFER_API_KEY` di `.env` lokal Anda |

### Rekomendasi tambahan (production)

| Prioritas | Rekomendasi |
|-----------|-------------|
| Tinggi | Deploy di belakang **HTTPS** (TLS); set `APP_URL` ke domain HTTPS |
| Tinggi | `SESSION_SECURE_COOKIE=true` dan `SESSION_SAME_SITE=lax` (atau `strict`) di production |
| Tinggi | Ganti password default seeder (`password123`) segera setelah deploy |
| Sedang | Batasi akses Reverb/WebSocket ke jaringan internal atau VPN jika dashboard tidak perlu publik |
| Sedang | Backup database terjadwal dan rotasi credential MySQL |
| Sedang | Aktifkan logging (`LOG_LEVEL=warning` di production) dan pantau percobaan login gagal |
| Opsional | Whitelist IP untuk `/api/submit-offer` di reverse proxy (Nginx/Cloudflare) |
| Opsional | Pindahkan API penawaran ke `routes/api.php` + Laravel Sanctum jika nanti ada aplikasi mobile resmi |

### Yang sengaja tidak dipasang (agar cocok untuk demo / Postman)

- **CSRF pada `/api/submit-offer`** — dikecualikan karena klien eksternal (Postman) mengirim JSON tanpa session cookie. Kompensasi: API Key + rate limit.
- **Autentikasi session pada API penawaran** — diganti API Key agar integrasi pihak ketiga tetap sederhana tanpa login admin.

### Akses endpoint lewat browser (mengetik URL)

Memang **boleh mengetik URL** di address bar — itu cara normal web bekerja. Keamanan **bukan** menyembunyikan alamat, melainkan **memeriksa session login** di setiap request.

| URL yang diketik | Tanpa login (incognito / logout) | Sudah login sebagai admin |
|------------------|----------------------------------|---------------------------|
| `/login` | ✅ Tampil form login | ↪️ Dialihkan ke `/dashboard` |
| `/dashboard` | ↪️ Dialihkan ke `/login` | ✅ Tampil dashboard |
| `/infentory` | ↪️ Dialihkan ke `/login` | ✅ Tampil inventori |
| `/sales` | ↪️ Dialihkan ke `/login` | ✅ Tampil sales |
| `POST /api/submit-offer` | ❌ Butuh `X-API-Key` (bukan session browser) | Sama — API terpisah |

**Cara uji cepat:**

1. Klik **Logout** di sidebar (atau tutup semua tab lalu buka **jendela incognito**).
2. Ketik `http://127.0.0.1:8000/infentory` di browser.
3. Hasil yang benar: langsung **redirect ke halaman login**, bukan tabel inventori.

Jika inventori tetap terbuka tanpa login, jalankan `php artisan config:clear` dan pastikan `php artisan serve` sudah di-restart.

> **Catatan:** Mengetik URL hanya mengirim **GET**. Aksi berbahaya (hapus mobil, terima penawaran) memakai **POST/PATCH/DELETE** + **CSRF token** dari form — tidak bisa dieksekusi hanya dengan mengetik alamat di browser.

### Real-time (Reverb)

- Channel `admin-dashboard` bersifat **publik** (cocok untuk backoffice internal).
- Untuk production multi-tenant, pertimbangkan `PrivateChannel` + otorisasi di `routes/channels.php` dan token Echo terautentikasi.

---

## 11. Lisensi

Proyek ini dibangun di atas framework Laravel (MIT). Logika bisnis dan antarmuka khusus disediakan untuk backoffice showroom AutoDeals.
