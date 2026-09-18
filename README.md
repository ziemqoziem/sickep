# SICKEP — Sistem Informasi Cuti Kepegawaian

Aplikasi web internal Pemerintah Kabupaten Klaten untuk memantau, mengelola, dan
menganalisis data kepegawaian dan riwayat cuti pegawai secara terpusat. Dibangun
dengan Laravel 12, dan menyinkronkan data dari sumber legacy **SIMABSARA2017**
(SQL Server) melalui ODBC ke database lokal (MySQL).

## Fitur Utama

- **Sinkronisasi data sumber** — menarik data OPD, unit kerja, pegawai, kode
  cuti, dan riwayat cuti dari database SQL Server legacy (`SIMABSARA2017`) via
  ODBC. Mendukung mode *incremental* (berbasis watermark `modidatetime`, hanya
  untuk entitas yang mendukungnya), *full scan*, dan *prune* (hapus data lokal
  yang sudah tidak ada di sumber, khusus riwayat cuti).
- **Dashboard & ringkasan cuti** (`/summary-cuti`) — KPI cuti pegawai per OPD,
  jenis cuti, dan status.
- **Pencarian cuti** (`/cari-cuti`) — telusuri riwayat cuti berdasarkan nama,
  NIP, unit kerja, jenis cuti, dan rentang tanggal.
- **Data OPD & Pegawai (mirror sumber)** (`/data/opd`, `/data/pegawai`) — hasil
  sinkronisasi dari SIMABSARA2017, read-only, dengan aksi admin untuk
  menonaktifkan, mengaktifkan kembali, dan memutasi pegawai (tercatat di log
  `pegawai_status_logs` / `pegawai_mutasi_logs`).
- **Setting Master** (`/master/*`, khusus admin) — pengelolaan data mandiri
  yang independen dari hasil sync, dengan CRUD penuh:
  - **Unit Kerja** — kelola OPD/unit kerja, kepala unit (dengan pencarian
    pegawai untuk auto-isi NIP/pangkat/golongan kepala).
  - **Master Pegawai** — kelola data pegawai (NIP & nama terkunci setelah
    dibuat), golongan/pangkat, jenis jabatan, OPD induk (dengan pencarian
    ketik), serta aksi non-aktifkan/aktifkan dan mutasi antar-OPD (tercatat
    di log `master_pegawai_status_logs` / `master_pegawai_mutasi_logs`).
  - **Pengguna** — kelola akun aplikasi (nama, email, role, password), dengan
    proteksi agar admin tidak bisa menghapus/menurunkan peran akun sendiri
    atau menghapus admin terakhir.
- **Setting Koneksi** (`/settings/koneksi`, khusus admin) — atur & uji koneksi
  ke database sumber langsung dari UI.
- **Role & akses** — dua peran (`admin`, `user`); fitur sinkronisasi dan
  Setting Master hanya bisa diakses admin.

## Tech Stack

- **Backend:** Laravel 12, PHP 8.2+
- **Autentikasi:** Laravel Breeze (Blade stack)
- **Frontend:** Blade, Tailwind CSS 3, Alpine.js, Vite
- **Database aplikasi:** MySQL (mirror data sumber + tabel master mandiri)
- **Database sumber:** SQL Server via `pdo_odbc` (driver "ODBC Driver 11 for
  SQL Server")

## Instalasi

```bash
git clone https://github.com/ziemqoziem/sickep.git
cd sickep

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Atur koneksi database aplikasi (`DB_*`) dan kredensial database sumber
(`MDB_*`) di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sicuper
DB_USERNAME=root
DB_PASSWORD=

MDB_DRIVER="ODBC Driver 11 for SQL Server"
MDB_HOST=
MDB_PORT=1433
MDB_DATABASE=
MDB_USERNAME=
MDB_PASSWORD=
```

Jalankan migrasi (akun awal `admin@klaten.go.id` / `password123` otomatis
dibuat) dan build asset:

```bash
php artisan migrate
php artisan storage:link
npm run build   # atau `npm run dev` untuk mode pengembangan
```

> **Catatan:** tabel `tb_opd_aktif` dan `tb_pegawai_aktif` (dipakai oleh modul
> Setting Master → Unit Kerja & Master Pegawai) dikelola di luar migrasi
> Laravel pada environment ini. Di environment baru, kedua tabel ini perlu
> disiapkan secara manual dengan struktur yang sesuai sebelum modul Setting
> Master dapat digunakan.

Jalankan server pengembangan:

```bash
php artisan serve
```

## Sinkronisasi Data

Jalankan sinkronisasi manual via Artisan:

```bash
# Sinkronisasi semua entitas (incremental)
php artisan sync:data all

# Sinkronisasi satu entitas
php artisan sync:data riwayatcuti

# Full scan (abaikan watermark)
php artisan sync:data all --full

# Full scan + hapus baris lokal yang sudah tidak ada di sumber (khusus riwayatcuti)
php artisan sync:data riwayatcuti --prune
```

Atau lewat UI di menu **Srimanganti → Sync Data** (khusus admin), yang juga
menampilkan status, jumlah baris, dan waktu sinkronisasi terakhir per entitas.

## Struktur Direktori Penting

```
app/Http/Controllers/
├── Data/           # Data hasil sync (read-only + aksi nonaktifkan/mutasi)
├── Master/         # CRUD Setting Master (Unit Kerja, Pengguna, Master Pegawai)
├── Sync/           # Kontrol sinkronisasi data sumber
├── Settings/       # Setting koneksi database sumber
├── Dashboard/       # Ringkasan/KPI cuti
└── Cuti/           # Pencarian cuti

app/Services/
├── MdbConnection.php   # Koneksi ODBC ke database sumber
└── SyncService.php     # Logika sinkronisasi per entitas

app/Console/Commands/
└── SyncDataCommand.php # Perintah `php artisan sync:data`
```

## Lisensi

Proyek internal Pemerintah Kabupaten Klaten. Dibangun di atas [Laravel](https://laravel.com), open-source software berlisensi [MIT](https://opensource.org/licenses/MIT).
