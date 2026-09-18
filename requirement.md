# Requirement: Sistem Pengajuan & Persetujuan Cuti (Cuti Baru)

Dokumen skema/prosedur untuk fitur pengajuan cuti pegawai di SICKEP (menu
sidebar **"Cuti Baru"**), sebelum masuk tahap implementasi. Aturan bisnis
mengacu ke [`ketentuan-cuti.md`](ketentuan-cuti.md).

> Status: **draft desain** — belum ada kode yang dibuat dari dokumen ini.
> Bagian [11. Asumsi & Pertanyaan Terbuka](#11-asumsi--pertanyaan-terbuka)
> berisi hal-hal yang perlu dikonfirmasi sebelum implementasi dimulai.

## Daftar Isi

1. [Ringkasan](#1-ringkasan)
2. [Peran & Hak Akses](#2-peran--hak-akses)
3. [Alur Proses Pengajuan Cuti](#3-alur-proses-pengajuan-cuti)
4. [Validasi Sisa & Kelayakan Cuti](#4-validasi-sisa--kelayakan-cuti)
5. [Skema Database](#5-skema-database)
6. [Diagram Relasi Tabel](#6-diagram-relasi-tabel)
7. [Dokumen PDF & QR Code Verifikasi](#7-dokumen-pdf--qr-code-verifikasi)
8. [Struktur Halaman & Menu](#8-struktur-halaman--menu)
9. [Otorisasi (Middleware/Policy)](#9-otorisasi-middlewarepolicy)
10. [Kebutuhan Teknis Tambahan](#10-kebutuhan-teknis-tambahan)
11. [Asumsi & Pertanyaan Terbuka](#11-asumsi--pertanyaan-terbuka)

---

## 1. Ringkasan

Pegawai mengajukan cuti secara digital → berjalan lewat **alur persetujuan
berjenjang** → sistem memvalidasi kelayakan & sisa cuti otomatis → setelah
disetujui di semua jenjang, sistem menerbitkan **dokumen cuti resmi
berformat PDF** yang memuat **QR code unik** untuk verifikasi keaslian.

| Aspek | Ketentuan |
|---|---|
| Pemohon | Selalu **pegawai (User)** sendiri |
| Jenjang 1 (semua jenis cuti) | **Atasan Langsung** pegawai pemohon |
| Jenjang 2 (semua jenis cuti) | **Kepala Unit Kerja / OPD Induk** (akun `opd`) |
| Jenjang 3 (khusus Cuti Besar & CLTN) | **Admin** — persetujuan akhir, hanya bisa diproses **setelah** jenjang 1 & 2 disetujui. Dibangun sebagai **modul terpisah** ("Persetujuan Akhir Cuti Besar & CLTN"), bukan bagian dari inbox "Persetujuan Saya" — lihat bagian 8 |
| Validasi sisa/kelayakan cuti | Otomatis, berdasar aturan di `ketentuan-cuti.md` |
| Output setelah disetujui semua jenjang | PDF surat cuti + QR code unik per pengajuan |

---

## 2. Peran & Hak Akses

Peran ditambahkan sebagai nilai baru pada `users.role` (saat ini
`enum('admin','user')` → menjadi `enum('admin','opd','user')`).

| Peran | Kode `role` | Cakupan | Bisa apa saja |
|---|---|---|---|
| **Admin** | `admin` | Seluruh sistem | Akses penuh: kelola master data (sudah ada), approve **semua** jenis cuti di jenjang mana pun (sebagai override), approve jenjang akhir **Cuti Besar & CLTN**, lihat semua pengajuan, kelola aturan jenis cuti |
| **OPD** (Kepala Unit Kerja Induk) | `opd` | Satu (atau beberapa) OPD tertentu | Approve/tolak pengajuan cuti di **jenjang 2** untuk pegawai di OPD yang diampu, lihat riwayat & rekap cuti OPD-nya |
| **User** | `user` | Diri sendiri | Ajukan cuti, lihat riwayat & sisa cuti sendiri, unduh/cetak PDF cuti yang sudah disetujui |

Pemetaan **user ↔ OPD yang diampu** memakai tabel pivot baru (lihat
[`opd_admins`](#51-opd_admins)) supaya satu admin OPD bisa mengampu lebih
dari satu unit kerja (mis. OPD kecil yang digabung sementara), dan supaya
tidak perlu mengubah struktur `users` secara kaku.

**Atasan Langsung bukan peran sistem (`role`) tersendiri**, dan **bukan
pemetaan tetap per pegawai** (memetakan atasan langsung satu per satu untuk
12 ribuan pegawai secara manual tidak realistis). Sebagai gantinya, dipakai
**skema terbuka**: saat mengajukan cuti, pemohon memilih sendiri atasan
langsungnya dari daftar kandidat yang difilter otomatis oleh sistem:

- Pegawai dengan `jenis_jabatan = 'Jabatan Struktural'`
- Berada di **OPD yang sama** dengan pemohon (`opd_id` sama)
- **Bukan** Kepala OPD dari OPD tersebut (dikecualikan karena Kepala OPD
  sudah berperan di Jenjang 2 — lihat catatan teknis di bagian 5.4)
- (disarankan) bukan pemohon itu sendiri

Pilihan ini disimpan **per pengajuan** (kolom
`cuti_pengajuan.atasan_langsung_pegawai_id`, lihat bagian 5.4) — bukan
disimpan permanen di data pegawai — sehingga tidak perlu pekerjaan
pemetaan massal di muka, dan pemohon bebas memilih pejabat struktural mana
pun di OPD-nya untuk pengajuan tertentu (mis. kalau ada lebih dari satu
Kepala Bidang/Kepala Seksi). Wewenang approve Jenjang 1 mengikuti pilihan
ini, bukan `role`.

---

## 3. Alur Proses Pengajuan Cuti

Semua jenis cuti melewati **jenjang 1 (Atasan Langsung)** dan **jenjang 2
(Kepala Unit Kerja/OPD Induk)** secara berurutan. Khusus **Cuti Besar** dan
**CLTN**, setelah lolos jenjang 1 & 2, ditambah **jenjang 3 (Admin)**
sebagai persetujuan akhir. Penolakan di **jenjang mana pun** langsung
menghentikan alur (tidak lanjut ke jenjang berikutnya).

```
Pegawai (User)
   │
   │ 1. Isi form pengajuan cuti (jenis, tanggal, alasan, lampiran jika perlu)
   ▼
Sistem memvalidasi kelayakan & sisa cuti (lihat bag. 4)
   │
   ├── Tidak lolos validasi ──► Ditolak otomatis, pegawai diminta perbaiki
   │
   ▼ Lolos validasi
Status: DIAJUKAN
Sistem membuat baris tahap persetujuan (cuti_pengajuan_tahap):
   - Tahap 1: Atasan Langsung           (selalu)
   - Tahap 2: Kepala Unit Kerja (OPD)   (selalu)
   - Tahap 3: Admin                     (hanya jika jenis = Cuti Besar / CLTN)
   │
   ▼
┌─────────────────────────── JENJANG 1 ───────────────────────────┐
│ Menunggu ATASAN LANGSUNG yang DIPILIH PEMOHON saat mengajukan     │
│  (dari daftar pejabat struktural di OPD yang sama, bukan Kepala  │
│   OPD -- lihat bagian 2 & 5.4)                                   │
└───────────────┬───────────────────────────────┬──────────────────┘
                 ▼ Setuju                        ▼ Tolak
┌─────────────────────────── JENJANG 2 ───────────────────────────┐   Status: DITOLAK
│ Menunggu KEPALA UNIT KERJA / OPD INDUK (akun role `opd`)         │   (alur berhenti,
│  yang mengampu OPD induk pegawai (via opd_admins)                │    pegawai bisa
└───────────────┬───────────────────────────────┬──────────────────┘    ajukan ulang)
                 ▼ Setuju                        ▼ Tolak
     Jenis cuti = Besar / CLTN ?                 │
                 │                               │
        ┌────────┴────────┐                      │
        ▼ Ya               ▼ Tidak                │
┌──── JENJANG 3 ────┐       │                      │
│ Menunggu ADMIN     │      │                      │
│ (persetujuan akhir)│      │                      │
└──────┬──────┬──────┘      │                      │
       ▼Setuju ▼Tolak───────┼──────────────────────┘
       │                    │
       ▼                    ▼
            Status: DISETUJUI
                 │
                 ▼
   Sistem generate nomor SK + PDF surat cuti + QR code unik
   Saldo/kelayakan cuti pegawai diperbarui (mis. sisa cuti tahunan dikurangi)
                 │
                 ▼
   Pegawai, Atasan Langsung, OPD & Admin dapat mengunduh/mencetak PDF resmi
```

**Catatan alur:**
- **Admin** selalu bisa melihat & melakukan *override* approve/tolak pada
  tahap mana pun (sesuai "akses penuh sistem"), meski secara normal Admin
  hanya bertindak di jenjang 3 (Besar/CLTN).
- Pengajuan yang masih menunggu di **jenjang mana pun** dapat **dibatalkan
  sendiri** oleh pegawai sebelum jenjang itu diputuskan.
- Setiap keputusan per jenjang dicatat sebagai satu baris di
  [`cuti_pengajuan_tahap`](#55-cuti_pengajuan_tahap) (siapa, kapan,
  setuju/tolak, catatan) — bukan cuma status tunggal — supaya jejak
  persetujuan berjenjang bisa ditampilkan penuh di PDF & riwayat.
- "Giliran approve siapa saat ini" cukup dicari lewat baris
  `cuti_pengajuan_tahap` dengan `urutan` terkecil yang masih berstatus
  `menunggu` untuk pengajuan tsb — tidak perlu kolom status terpisah di
  `cuti_pengajuan`.

---

## 4. Validasi Sisa & Kelayakan Cuti

Validasi dijalankan saat pengajuan dibuat (dan idealnya dicek ulang saat
disetujui, untuk menghindari kondisi balapan/race antar pengajuan). Aturan
per jenis cuti mengikuti `ketentuan-cuti.md`:

| Jenis Cuti | Validasi masa kerja | Validasi jatah/plafon | Sumber data jatah |
|---|---|---|---|
| Cuti Tahunan | ≥ 1 tahun kerja terus-menerus | 12 hari/tahun + carry-over maks. 6 hari dari tahun lalu (24 hari jika ada penangguhan tahun sebelumnya) | Ledger [`cuti_saldo_tahunan`](#53-cuti_saldo_tahunan) |
| Cuti Besar | ≥ 5 tahun kerja terus-menerus (kecuali haji pertama kali / kelahiran anak ke-4+) | Maks. 3 bulan; **tidak boleh** mengambil Cuti Tahunan di tahun yang sama | Dihitung dinamis dari riwayat `cuti_pengajuan` disetujui |
| Cuti Sakit | Tidak disyaratkan | Maks. 1 tahun (+6 bulan perpanjangan dg surat tim penguji kesehatan); keguguran maks. 1,5 bulan | Dihitung dinamis dari riwayat `cuti_pengajuan` disetujui (akumulasi dalam periode berjalan) |
| Cuti Melahirkan | Tidak disyaratkan | Maks. 3 bulan; anak ke-4+ pakai skema Cuti Besar | Dihitung dari riwayat + `keterangan_anak_ke` pada pengajuan |
| Cuti Alasan Penting | Tidak disyaratkan | Maks. 1 bulan | Dihitung dinamis |
| Cuti Bersama | Tidak disyaratkan | Mengikuti kalender cuti bersama nasional (tidak mengurangi cuti tahunan) | Tabel referensi kalender cuti bersama (lihat pertanyaan terbuka) |
| CLTN | ≥ 5 tahun kerja terus-menerus | Maks. 3 tahun + perpanjangan 1 tahun | Dihitung dinamis; hanya boleh 1 CLTN aktif per pegawai |

**Aturan silang (cross-validation) yang perlu dicek sistem:**
- Tidak boleh ada 2 pengajuan cuti dengan **rentang tanggal tumpang tindih**
  yang berstatus disetujui/menunggu untuk pegawai yang sama.
- Pegawai yang sedang menjalani **Cuti Besar** tidak boleh mengajukan
  **Cuti Tahunan** di tahun berjalan yang sama.
- Pegawai yang sedang **CLTN** tidak boleh mengajukan jenis cuti lain
  (karena statusnya diberhentikan sementara dari jabatan).
- Dokumen pendukung wajib diperiksa kelengkapannya sebelum pengajuan bisa
  diteruskan ke penyetuju (lihat kolom `perlu_dokumen` pada
  [`jenis_cuti_aturan`](#52-jenis_cuti_aturan)).

Aturan detail per jenis cuti (jatah hari, syarat masa kerja, perlu dokumen,
level persetujuan) disimpan **data-driven** di tabel
[`jenis_cuti_aturan`](#52-jenis_cuti_aturan) — bukan *hard-code* di kode
program — supaya Admin bisa menyesuaikan lewat halaman Setting Master jika
suatu saat aturan BKN berubah.

---

## 5. Skema Database

### Tabel yang **sudah ada** dan dipakai ulang

| Tabel | Dipakai untuk |
|---|---|
| `tb_pegawai_aktif` | Data pemohon cuti **dan** sumber kandidat Atasan Langsung (filter `jenis_jabatan='Jabatan Struktural'` + `opd_id` sama + bukan Kepala OPD); kolom `sisa_cuti_tahunan` disinkronkan dari `cuti_saldo_tahunan` sebagai cache tampilan cepat. **Tidak perlu kolom baru** — skema terbuka ini sengaja menghindari pemetaan atasan langsung tetap per pegawai |
| `tb_opd_aktif` | Unit kerja induk pegawai; acuan cakupan Kepala Unit Kerja (Jenjang 2) |
| `tkodecuti` | Referensi jenis cuti hasil sync (dipetakan ke `jenis_cuti_aturan` via kolom `kodecuti`, opsional) |
| `users` | Akun pengguna; ditambah nilai role `opd` |

### Tabel **baru**

#### 5.1 `opd_admins`
Pivot: siapa yang menjadi Admin OPD untuk unit kerja mana.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| user_id | FK → users.id | Harus berrole `opd` |
| opd_id | FK → tb_opd_aktif.id | |
| created_at | timestamp | |

*Unique: (user_id, opd_id).*

#### 5.2 `jenis_cuti_aturan`
Master aturan cuti — data-driven, dikelola Admin (bagian dari Setting
Master).

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| kode | varchar(20) unik | Kode jenis cuti internal, mis. `TAHUNAN`, `BESAR`, `SAKIT`, `MELAHIRKAN`, `ALASAN_PENTING`, `BERSAMA`, `CLTN` |
| kodecuti_sumber | varchar(10) nullable | Pemetaan opsional ke `tkodecuti.kodecuti` (hasil sync) untuk keperluan pelaporan gabungan |
| nama | varchar(100) | Nama tampilan, mis. "Cuti Tahunan" |
| syarat_masa_kerja_bulan | int nullable | Mis. 12 (tahunan), 60 (besar/CLTN), null jika tidak disyaratkan |
| jatah_hari | int nullable | Jatah dasar per tahun (khusus Cuti Tahunan = 12) |
| carry_over_hari | int nullable | Maks. hari bisa dibawa ke tahun berikutnya (Cuti Tahunan = 6) |
| maks_hari | int nullable | Plafon maksimal per pengajuan/periode (Besar & Melahirkan = 90, Alasan Penting = 30, CLTN = 1095 + 365 perpanjangan) |
| perlu_dokumen | boolean | Apakah wajib unggah lampiran (Sakit, sebagian Alasan Penting) |
| butuh_persetujuan_admin | boolean default false | `true` hanya untuk Cuti Besar & CLTN — menambahkan Jenjang 3 (Admin) di alur bagian 3 |
| keterangan | text nullable | Ringkasan aturan untuk ditampilkan di form pengajuan |
| aktif | boolean default true | |
| timestamps | | |

#### 5.3 `cuti_saldo_tahunan`
Ledger saldo Cuti Tahunan per pegawai per tahun (satu-satunya jenis cuti
dengan skema jatah+carry-over tahunan).

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| pegawai_id | FK → tb_pegawai_aktif.id | |
| tahun | int | |
| jatah | int | Default 12 |
| carry_over_masuk | int | Dari sisa tahun sebelumnya, maks. 6 (atau 24 jika ada penangguhan — lihat `cuti_penangguhan` di bawah) |
| tambahan_cuti_bersama | int default 0 | Kompensasi bagi jabatan yang tidak dapat cuti bersama (poin 7 `ketentuan-cuti.md`) |
| terpakai | int default 0 | Akumulasi hari yang sudah disetujui |
| sisa | int | `jatah + carry_over_masuk + tambahan_cuti_bersama - terpakai` (bisa kolom generated atau dihitung di aplikasi) |
| timestamps | | |

*Unique: (pegawai_id, tahun).*

#### 5.4 `cuti_pengajuan`
Tabel inti — satu baris per pengajuan cuti.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| nomor_pengajuan | varchar(50) unik | Nomor referensi internal, mis. `CT-2026-000123` |
| pegawai_id | FK → tb_pegawai_aktif.id | Pemohon |
| atasan_langsung_pegawai_id | FK → tb_pegawai_aktif.id | Dipilih pemohon saat mengajukan, dari kandidat terfilter (lihat bagian 2). Disimpan per pengajuan, **bukan** field tetap di data pegawai |
| jenis_cuti_aturan_id | FK → jenis_cuti_aturan.id | |
| tanggal_mulai | date | |
| tanggal_selesai | date nullable | Nullable untuk kasus cuti sakit tanpa kepastian tanggal selesai di awal |
| lama_hari | int | Dihitung sistem (hari kerja atau kalender sesuai aturan jenis cuti) |
| alasan | text | |
| alamat_selama_cuti | varchar(255) nullable | Sesuai format baku surat cuti BKN |
| telepon_selama_cuti | varchar(30) nullable | |
| keterangan_anak_ke | tinyint nullable | Khusus Cuti Melahirkan, untuk deteksi anak ke-4+ |
| status | enum('diajukan','disetujui','ditolak','dibatalkan') | Status akhir keseluruhan; detail per jenjang ada di `cuti_pengajuan_tahap` |
| jumlah_tahap | tinyint | Snapshot jumlah jenjang saat pengajuan dibuat (2 untuk cuti biasa, 3 untuk Besar/CLTN) — disalin dari `jenis_cuti_aturan.butuh_persetujuan_admin` supaya perubahan aturan di kemudian hari tidak mengubah alur pengajuan lama |
| nomor_sk | varchar(50) nullable | Diisi saat disetujui (jenjang terakhir) |
| tanggal_sk | date nullable | |
| qr_token | uuid/char(36) unik | Token unik untuk QR code (lihat bag. 7) |
| pdf_path | varchar(255) nullable | Lokasi file PDF yang sudah digenerate |
| pdf_generated_at | timestamp nullable | |
| dibuat_oleh | FK → users.id | Biasanya = akun pegawai sendiri |
| timestamps | | |

*Index: (pegawai_id, status), (jenis_cuti_aturan_id), (tanggal_mulai, tanggal_selesai) untuk cek tumpang-tindih.*

> **Catatan teknis — kandidat Atasan Langsung & deteksi Kepala OPD:**
> kolom `tb_opd_aktif.kepala_pegawai_id` yang seharusnya jadi acuan "siapa
> Kepala OPD" ternyata **foreign key rusak** (mengarah ke tabel `pegawai`
> yang tidak ada lagi di database, ditemukan saat membangun modul Unit
> Kerja) — kolom ini **tidak pernah bisa diisi** dan selalu `NULL`. Deteksi
> "pegawai ini Kepala OPD atau bukan" untuk keperluan filter kandidat
> Atasan Langsung harus memakai pencocokan teks
> `tb_pegawai_aktif.nip = tb_opd_aktif.nip_kepala`, bukan
> `kepala_pegawai_id`. Query kandidat Atasan Langsung untuk seorang
> pemohon kira-kira:
> ```sql
> SELECT * FROM tb_pegawai_aktif
> WHERE opd_id = :opd_id_pemohon
>   AND jenis_jabatan = 'Jabatan Struktural'
>   AND id != :id_pemohon
>   AND nip NOT IN (SELECT nip_kepala FROM tb_opd_aktif WHERE nip_kepala IS NOT NULL)
> ```

#### 5.5 `cuti_pengajuan_tahap`
Satu baris per jenjang persetujuan per pengajuan — inti dari alur
berjenjang di bagian 3. Dibuat otomatis (2 atau 3 baris) saat
`cuti_pengajuan` dibuat.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| cuti_pengajuan_id | FK → cuti_pengajuan.id | |
| urutan | tinyint | 1 = Atasan Langsung, 2 = Kepala Unit Kerja, 3 = Admin (jika ada) |
| jenjang | enum('atasan_langsung','kepala_unit_kerja','admin') | |
| status | enum('menunggu','disetujui','ditolak','dilewati') | `dilewati` dipakai untuk jenjang setelahnya saat pengajuan ditolak lebih awal |
| penyetuju_id | FK → users.id, nullable | Diisi saat baris ini diputuskan |
| diputuskan_pada | timestamp nullable | |
| catatan | text nullable | Alasan tolak / catatan approve pada jenjang ini |
| created_at | timestamp | |

*Unique: (cuti_pengajuan_id, urutan). Index: (jenjang, status) untuk query "daftar tugas approval milik saya".*

#### 5.6 `cuti_pengajuan_lampiran`
Dokumen pendukung (bisa lebih dari satu file per pengajuan).

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| cuti_pengajuan_id | FK → cuti_pengajuan.id | |
| nama_dokumen | varchar(150) | Mis. "Surat Keterangan Dokter" |
| path_file | varchar(255) | |
| diunggah_oleh | FK → users.id | |
| created_at | timestamp | |

#### 5.7 `cuti_pengajuan_log`
Jejak audit setiap perubahan status **keseluruhan** pengajuan (di luar
detail per-jenjang yang sudah tercakup di `cuti_pengajuan_tahap`) — mis.
saat status akhir berubah jadi `disetujui`/`ditolak`/`dibatalkan`.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| cuti_pengajuan_id | FK → cuti_pengajuan.id | |
| status_sebelum | varchar(20) nullable | |
| status_sesudah | varchar(20) | |
| oleh | FK → users.id | |
| catatan | text nullable | |
| created_at | timestamp | |

---

## 6. Diagram Relasi Tabel

```
users ──┬───< opd_admins >───── tb_opd_aktif ──< tb_pegawai_aktif
        │                          (nip_kepala dipakai deteksi                │
        │                           Kepala OPD, lihat catatan 5.4)            │
        └──< cuti_pengajuan (dibuat_oleh)                                     │
                     │  ▲                                                     │
                     │  ├──────── pegawai_id ─────────────────────────────────┤
                     │  └──────── atasan_langsung_pegawai_id ─────────────────┘
                     │            (dipilih per pengajuan, dari kandidat
                     │             struktural OPD yang sama)
                     │
                     ├──< cuti_pengajuan_tahap >── users (penyetuju_id)
                     ├──< cuti_pengajuan_lampiran
                     ├──< cuti_pengajuan_log
                     └──> jenis_cuti_aturan

tb_pegawai_aktif ──< cuti_saldo_tahunan (pegawai_id, tahun)
```

---

## 7. Dokumen PDF & QR Code Verifikasi

> **Status template: belum tersedia.** Bagian ini baru mendefinisikan
> *data* yang wajib ada di dokumen (daftar di bawah), **bukan** tata
> letak/desain visual resminya — template formulir cuti akan disiapkan
> dan diberikan menyusul. Implementasi tampilan PDF (`develop.md` Fase
> 5.2) memakai layout sementara/placeholder dulu memakai data ini, lalu
> diganti begitu template resmi tersedia; langkah-langkah lain di Fase 5
> (dependency, generate & simpan file, halaman verifikasi, tombol unduh)
> tidak perlu menunggu dan bisa tetap jalan.

- PDF **hanya digenerate setelah status `disetujui`** (bukan saat draft),
  supaya dokumen yang beredar selalu merepresentasikan keputusan final.
- Format PDF **rencananya** mengikuti **Lampiran II Perban BKN No.
  7/2021** (format permintaan, pertimbangan, dan keputusan cuti), ditambah
  kop surat Pemerintah Kabupaten Klaten (logo, sama seperti halaman
  Rekapitulasi) — menunggu konfirmasi template resmi di atas.
- Isi minimal PDF: nomor SK/surat, identitas pegawai (nama, NIP, jabatan,
  unit kerja), jenis cuti, tanggal mulai–selesai, lama hari, alasan,
  alamat/telepon selama cuti, nama & jabatan penyetuju, tanggal
  keputusan, **QR code** di pojok dokumen.
- **QR code** meng-encode URL verifikasi publik:
  `https://<domain-sickep>/verifikasi-cuti/{qr_token}` — token per
  pengajuan (`cuti_pengajuan.qr_token`, UUID acak, bukan ID berurutan yang
  mudah ditebak).
- Halaman verifikasi (`/verifikasi-cuti/{token}`) **tidak memerlukan
  login** (diakses siapa pun yang memindai dokumen fisik), menampilkan
  ringkasan read-only: nama, NIP, jenis cuti, tanggal, status **"Sah/
  Terverifikasi"**, nomor SK, dan siapa yang menyetujui — untuk membantu
  memastikan dokumen cetak belum diubah/dipalsukan. Halaman ini **tidak**
  menampilkan data sensitif lain (alasan, alamat, telepon).
- Rekomendasi teknis pembuatan PDF & QR ada di [bagian 10](#10-kebutuhan-teknis-tambahan).

---

## 8. Struktur Halaman & Menu

Sidebar sudah punya placeholder grup **"Cuti Baru"** (Menu 1–4) yang bisa
dipetakan ulang:

| Menu | Rute (usulan) | Siapa yang lihat |
|---|---|---|
| Ajukan Cuti | `/cuti-baru/ajukan` | User |
| Riwayat Cuti Saya | `/cuti-baru/riwayat` | User |
| Persetujuan Saya | `/cuti-baru/persetujuan` | **Semua role** — inbox untuk **jenjang 1 & 2 saja**: pengajuan yang menunggu tahap Atasan Langsung milik user ybs, atau menunggu tahap Kepala Unit Kerja (jika role `opd`). Admin tetap bisa melihat/override baris di sini juga, tapi menu ini **tidak** menampilkan tahap jenjang 3 |
| **Persetujuan Akhir Cuti Besar & CLTN** | `/cuti-baru/persetujuan-akhir` | **Modul terpisah, khusus Admin.** Hanya menampilkan pengajuan Cuti Besar/CLTN yang **sudah disetujui** di jenjang 1 & 2 dan sedang menunggu jenjang 3 (Admin) — sesuai permintaan agar persetujuan akhir tetap diproses Admin *setelah* disetujui Admin OPD. Menampilkan jejak lengkap keputusan jenjang 1 & 2 (siapa, kapan, catatan) supaya Admin punya konteks penuh sebelum memutuskan. Controller & view berdiri sendiri (`PersetujuanAkhirCutiController`), bukan cabang dari controller Persetujuan Saya — lihat `develop.md` Fase 4.5 |
| Detail Pengajuan + Cetak PDF | `/cuti-baru/{id}` | User (miliknya sendiri), siapa pun yang jadi/pernah jadi penyetuju tahapnya, Admin (semua) |
| Verifikasi QR (publik) | `/verifikasi-cuti/{token}` | Semua orang (tanpa login) |
| Master Aturan Cuti | `/master/jenis-cuti` | Admin (pola sama dengan Setting Master yang sudah ada) |

Untuk **User**, ringkasan sisa cuti tahunan (dari `cuti_saldo_tahunan`)
ditampilkan di halaman Riwayat Cuti Saya, senada kartu ringkasan yang
sudah dipakai di Rekapitulasi/Cari Data Cuti.

---

## 9. Otorisasi (Middleware/Policy)

- Middleware `admin` (sudah ada) tetap dipakai untuk halaman khusus Admin.
- Middleware baru `opd` (`EnsureUserIsOpdAdmin`) untuk halaman yang boleh
  diakses Admin OPD **dan** Admin (super admin selalu boleh masuk).
- Logika **siapa boleh approve tahap X dari pengajuan Y** sebaiknya di satu
  tempat (Laravel Policy `CutiPengajuanTahapPolicy::approve()`), bukan
  tersebar di controller, karena wewenangnya berbeda per jenjang:
  ```
  boleh approve baris cuti_pengajuan_tahap jika:
    role === 'admin'                                    // admin selalu boleh override
    ATAU (
      tahap.jenjang === 'atasan_langsung'
      DAN user login terhubung (lewat users.pegawai_id) ke pegawai yang
          tercatat sebagai cuti_pengajuan.atasan_langsung_pegawai_id
          pada pengajuan ini (pilihan pemohon saat submit, bukan relasi
          tetap)
    )
    ATAU (
      tahap.jenjang === 'kepala_unit_kerja'
      DAN role === 'opd'
      DAN user mengampu (via opd_admins) OPD induk pegawai pemohon
    )
    ATAU (
      tahap.jenjang === 'admin'
      DAN role === 'admin'
    )

  DAN tahap yang dimaksud harus baris `urutan` TERKECIL yang masih
  berstatus 'menunggu' untuk pengajuan tsb (tidak bisa approve tahap 2
  sebelum tahap 1 diputuskan).
  ```
- Pegawai hanya boleh melihat/mengajukan/membatalkan pengajuan miliknya
  sendiri (dicek lewat `pegawai_id` ↔ akun user yang login — perlu
  dipastikan ada relasi `users` ↔ `tb_pegawai_aktif`, lihat pertanyaan
  terbuka).
- **Modul "Persetujuan Akhir Cuti Besar & CLTN"** dilindungi middleware
  `admin` biasa (bukan lewat `CutiPengajuanTahapPolicy` yang dipakai modul
  "Persetujuan Saya"), karena aksesnya memang eksklusif Admin dan tidak
  perlu logika bersyarat gabungan seperti jenjang 1/2. Query-nya juga
  dibatasi ganda: `jenjang = 'admin'` **dan** kedua tahap sebelumnya
  (`urutan` 1 & 2) sudah berstatus `disetujui` — supaya modul ini
  konsisten dengan aturan "hanya boleh diproses setelah disetujui Admin
  OPD", bukan cuma mengandalkan urutan `cuti_pengajuan_tahap` yang benar.

---

## 10. Kebutuhan Teknis Tambahan

| Kebutuhan | Rekomendasi |
|---|---|
| Generate PDF di server | `barryvdh/laravel-dompdf` (paket Laravel paling umum, belum terpasang di proyek ini) |
| Generate QR code | `simplesoftwareio/simple-qrcode` (belum terpasang) |
| Penyimpanan file (PDF & lampiran) | Disk `public` yang sudah dipakai untuk foto profil (`storage/app/public`), folder baru `cuti-pengajuan/` dan `cuti-lampiran/` |
| Migrasi role | Migration `ALTER TABLE users MODIFY role ENUM('admin','opd','user')` |
| Migrasi tabel baru | 6 migration baru sesuai bagian 5 |
| Seed data | Isi awal `jenis_cuti_aturan` (7 baris sesuai `ketentuan-cuti.md`) |

---

## 11. Asumsi & Pertanyaan Terbuka

Hal-hal berikut saya asumsikan atau belum jelas dari permintaan — perlu
dikonfirmasi sebelum lanjut ke implementasi:

1. **Relasi akun login ↔ data pegawai**: saat ini `users` (akun login)
   dan `tb_pegawai_aktif` (data kepegawaian) adalah dua tabel terpisah
   tanpa relasi eksplisit. Perlu ditambahkan kolom penghubung (mis.
   `users.pegawai_id` atau `users.nip`) supaya sistem tahu "pegawai mana"
   yang sedang login saat mengajukan cuti. Ini prasyarat teknis sebelum
   fitur ini bisa jalan.
2. **Satu Admin OPD = satu OPD, atau bisa lebih dari satu?** Dokumen ini
   mengasumsikan **bisa lebih dari satu** (lewat tabel pivot
   `opd_admins`) untuk fleksibilitas. Jika ternyata selalu 1:1, tabel bisa
   disederhanakan jadi kolom `opd_id` langsung di `users`.
3. **Kalender Cuti Bersama**: `ketentuan-cuti.md` menyebut cuti bersama
   mengikuti keputusan pemerintah pusat tiap tahun. Apakah perlu tabel
   referensi tanggal cuti bersama per tahun, atau cukup diinput manual
   oleh Admin tiap kali ada Keppres baru?
4. **Format nomor pengajuan/nomor SK**: belum ada spesifikasi format
   resmi (Tata Naskah Dinas Pemkab Klaten). Draf ini pakai contoh
   `CT-2026-000123` — perlu disesuaikan dengan format resmi yang berlaku.
5. **PDF digenerate saat status apa?** Diasumsikan **hanya setelah
   disetujui**. Jika Admin OPD/Admin ingin mencetak draf sebelum
   diputuskan (untuk ditandatangani manual dulu, misalnya), perlu alur
   tambahan.
6. **Siapa yang menandatangani PDF?** Apakah cukup nama penyetuju digital
   (dari sistem), atau perlu tanda tangan elektronik bersertifikat (mis.
   BSrE/PSrE) agar sah secara hukum? Ini di luar cakupan skema data, tapi
   berdampak besar ke desain PDF.
7. **Notifikasi** (email/WA/bel di aplikasi saat status berubah) belum
   diminta secara eksplisit — dianggap **di luar cakupan v1**, tapi mudah
   ditambahkan belakangan karena semua perubahan status sudah tercatat di
   `cuti_pengajuan_log`.
8. **Validasi tumpang-tindih & saldo cuti Sakit/Besar/CLTN** dihitung
   dinamis dari riwayat `cuti_pengajuan` (bukan ledger tersendiri seperti
   Cuti Tahunan) — perlu dipastikan ini cukup performan mengingat volume
   data cuti historis (riwayatcuti hasil sync saja sudah 100 ribuan
   baris); mungkin perlu index tambahan atau caching saat implementasi.
9. **Bagaimana jika OPD pemohon tidak punya kandidat Atasan Langsung sama
    sekali** (mis. semua pegawai di OPD itu Jabatan Fungsional/Pelaksana,
    tidak ada Jabatan Struktural selain Kepala OPD sendiri)? Opsi yang
    disarankan: jenjang 1 otomatis **dilewati** (status `dilewati`) dan
    alur langsung ke jenjang 2, supaya pengajuan tidak macet. Perlu
    dikonfirmasi apakah ini perilaku yang diinginkan.
10. **Bagaimana jika kandidat Atasan Langsung yang dipilih pemohon belum
    punya akun login** (belum tertaut lewat `users.pegawai_id`, lihat
    prasyarat Fase 0.1 di `develop.md`)? Pengajuan tetap bisa dibuat, tapi
    jenjang 1 tidak akan pernah bisa diproses sampai akun pejabat tsb
    ditautkan — perlu validasi di form pengajuan untuk memperingatkan
    pemohon/Admin lebih awal (mis. sembunyikan kandidat yang belum punya
    akun dari daftar pilihan).
11. **Apakah pemohon bebas memilih siapa pun dari daftar kandidat, atau
    perlu validasi tambahan** (mis. tidak boleh memilih struktural dari
    sub-unit yang tidak relevan dengan jabatannya)? Draf ini mengasumsikan
    **bebas memilih siapa pun dari OPD yang sama** (skema terbuka sesuai
    permintaan), tanpa pemetaan hierarki bidang/seksi yang lebih detail.
12. **Jabatan Struktural pemohon sendiri**: jika pemohon berjabatan
    struktural, ia tetap muncul sebagai kandidat untuk pegawai lain di
    OPD yang sama, tapi dikecualikan dari daftar kandidat untuk
    pengajuannya sendiri (tidak boleh jadi atasan langsung diri sendiri).
