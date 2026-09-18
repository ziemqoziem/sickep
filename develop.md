# Rencana Pembangunan: Sistem Pengajuan & Persetujuan Cuti

Roadmap implementasi untuk `requirement.md` (acuan skema) dan
`ketentuan-cuti.md` (acuan aturan bisnis). Dipecah jadi langkah-langkah
kecil yang berurutan — tiap langkah ditulis sebagai **prompt siap-pakai**
(blok kode) yang bisa langsung ditempel ke sesi kerja berikutnya, satu per
satu, tanpa perlu mengulang konteks dari awal.

**Cara pakai:** kerjakan Fase 0 dulu sampai selesai sebelum lanjut ke fase
berikutnya — setiap fase bergantung pada fase sebelumnya. Dalam satu fase,
langkah-langkahnya juga berurutan (jangan loncat).

Centang `[ ]` → `[x]` di file ini setiap langkah selesai, supaya progres
mudah dilacak lintas sesi kerja.

---

## Fase 0 — Prasyarat Data

Tanpa fase ini, sistem tidak tahu "pegawai mana" yang login. **Wajib
selesai duluan.**

> Catatan: rencana awal sempat memasukkan langkah "kolom
> `atasan_langsung_id` per pegawai" di fase ini, tapi **dihapus** —
> memetakan atasan langsung satu per satu untuk 12 ribuan pegawai secara
> manual tidak realistis. Diganti skema terbuka (kandidat pejabat
> struktural per OPD, dipilih pemohon saat mengajukan) yang dibangun di
> Fase 3.1, bukan prasyarat data di muka. Lihat requirement.md bagian 2 &
> 5.4.

### 0.1 Relasi akun login ↔ data pegawai
- [x] Prompt:
  ```
  Tambahkan relasi antara tabel users (akun login) dan tb_pegawai_aktif
  (data kepegawaian). Buat migration untuk menambah kolom nullable
  pegawai_id (FK ke tb_pegawai_aktif.id, nullOnDelete) di tabel users.
  Update model User dengan relasi pegawai() (belongsTo MasterPegawai) dan
  model MasterPegawai dengan relasi user() (hasOne User). Tambahkan field
  "Pegawai Terhubung" di form edit Pengguna (/master/pengguna) berupa
  pencarian ketik (pola sama seperti pencarian kepala unit di Unit Kerja),
  supaya Admin bisa menautkan akun login ke data pegawai secara manual.
  ```
- **Kriteria selesai**: kolom `pegawai_id` ada di `users`, bisa diisi lewat
  halaman Master Pengguna, relasi Eloquent dua arah berfungsi.

### 0.2 Role `opd` + pivot `opd_admins`
- [x] Prompt:
  ```
  Ubah kolom users.role dari enum('admin','user') menjadi
  enum('admin','opd','user') lewat migration. Buat migration & model baru
  OpdAdmin untuk tabel pivot opd_admins (user_id FK users, opd_id FK
  tb_opd_aktif, unique user_id+opd_id). Tambahkan relasi opdDiampu()
  (belongsToMany MasterOpd via opd_admins) di model User, dan admins()
  (belongsToMany User via opd_admins) di model MasterOpd. Update dropdown
  "Peran" di form Master Pengguna (/master/pengguna) supaya menyertakan
  opsi "OPD". Saat role dipilih "OPD", tampilkan multi-select pencarian
  OPD (mengizinkan lebih dari satu) yang disimpan ke opd_admins.
  ```
- **Kriteria selesai**: user dengan role `opd` bisa diberi satu/lebih OPD
  yang diampu lewat halaman Master Pengguna.

---

## Fase 1 — Master Aturan Cuti (data-driven)

### 1.1 Migration & model `jenis_cuti_aturan`
- [x] Prompt:
  ```
  Buat migration & model Eloquent JenisCutiAturan untuk tabel
  jenis_cuti_aturan, sesuai spesifikasi lengkap di requirement.md bagian
  5.2 (kolom: kode, kodecuti_sumber, nama, syarat_masa_kerja_bulan,
  jatah_hari, carry_over_hari, maks_hari, perlu_dokumen,
  butuh_persetujuan_admin, keterangan, aktif, timestamps).
  ```

### 1.2 Seeder data awal
- [x] Prompt:
  ```
  Buat database seeder JenisCutiAturanSeeder yang mengisi 7 baris
  jenis_cuti_aturan sesuai ketentuan-cuti.md: Cuti Tahunan (syarat 12 bln,
  jatah 12 hari, carry_over 6 hari), Cuti Besar (syarat 60 bln, maks_hari
  90, butuh_persetujuan_admin true), Cuti Sakit (maks_hari 365, kolom
  keterangan sebutkan bisa +6 bulan dg surat tim penguji, perlu_dokumen
  true), Cuti Melahirkan (maks_hari 90), Cuti Karena Alasan Penting
  (maks_hari 30), Cuti Bersama (tanpa jatah/maks, tidak mengurangi cuti
  tahunan -- catat di keterangan), Cuti di Luar Tanggungan Negara (syarat
  60 bln, maks_hari 1095, butuh_persetujuan_admin true). Daftarkan di
  DatabaseSeeder lalu jalankan.
  ```

### 1.3 Halaman Setting Master → Aturan Cuti
- [x] Prompt:
  ```
  Buat halaman CRUD baru di Setting Master untuk mengelola
  jenis_cuti_aturan, mengikuti pola persis yang sudah dipakai di
  /master/unit-kerja (index.blade.php dengan modal create/edit Alpine,
  controller dengan rules() dan store/update, route grup
  ['auth','admin'] prefix master, item baru di sidebar grup "Setting
  Master"). Buat controller JenisCutiAturanController, route
  /master/jenis-cuti, dan view master/jenis-cuti/index.blade.php. Field
  form: kode, nama, syarat_masa_kerja_bulan, jatah_hari, carry_over_hari,
  maks_hari, perlu_dokumen (checkbox), butuh_persetujuan_admin (checkbox),
  keterangan (textarea), aktif (checkbox). Tabel tampilkan semua kolom
  ringkas + toggle aktif/nonaktif.
  ```
- **Kriteria Fase 1 selesai**: Admin bisa melihat & mengubah aturan 7
  jenis cuti dari UI, tanpa menyentuh kode.

---

## Fase 2 — Skema Database Pengajuan Cuti

### 2.1 Migration inti: saldo, pengajuan, tahap, lampiran, log
- [x] Prompt:
  ```
  Buat 5 migration baru sesuai spesifikasi lengkap di requirement.md
  bagian 5.3-5.7:
  1. cuti_saldo_tahunan (pegawai_id, tahun, jatah, carry_over_masuk,
     tambahan_cuti_bersama, terpakai, sisa, unique pegawai_id+tahun)
  2. cuti_pengajuan (nomor_pengajuan unik, pegawai_id,
     atasan_langsung_pegawai_id FK tb_pegawai_aktif, jenis_cuti_aturan_id,
     tanggal_mulai, tanggal_selesai nullable, lama_hari, alasan,
     alamat_selama_cuti, telepon_selama_cuti, keterangan_anak_ke nullable,
     status enum diajukan/disetujui/ditolak/dibatalkan, jumlah_tahap,
     nomor_sk, tanggal_sk, qr_token unik, pdf_path, pdf_generated_at,
     dibuat_oleh FK users, timestamps, index pegawai_id+status)
  3. cuti_pengajuan_tahap (cuti_pengajuan_id, urutan, jenjang enum
     atasan_langsung/kepala_unit_kerja/admin, status enum
     menunggu/disetujui/ditolak/dilewati, penyetuju_id FK users nullable,
     diputuskan_pada, catatan, unique cuti_pengajuan_id+urutan)
  4. cuti_pengajuan_lampiran (cuti_pengajuan_id, nama_dokumen, path_file,
     diunggah_oleh FK users)
  5. cuti_pengajuan_log (cuti_pengajuan_id, status_sebelum, status_sesudah,
     oleh FK users, catatan)
  Jangan buat model dulu di langkah ini -- migration saja.
  ```

### 2.2 Model Eloquent + relasi
- [x] Prompt:
  ```
  Buat 5 model Eloquent untuk tabel dari langkah 2.1: CutiSaldoTahunan,
  CutiPengajuan, CutiPengajuanTahap, CutiPengajuanLampiran,
  CutiPengajuanLog. Definisikan relasi lengkap: CutiPengajuan belongsTo
  MasterPegawai (pegawai_id), belongsTo JenisCutiAturan, belongsTo User
  (dibuat_oleh), hasMany CutiPengajuanTahap, hasMany
  CutiPengajuanLampiran, hasMany CutiPengajuanLog. CutiPengajuanTahap
  belongsTo CutiPengajuan, belongsTo User (penyetuju_id). Tambahkan
  accessor/helper di CutiPengajuan: tahapAktif() (ambil baris tahap
  urutan terkecil berstatus 'menunggu'), dan casts tanggal yang sesuai.
  ```
- **Kriteria Fase 2 selesai**: `php artisan migrate` berhasil tanpa error,
  semua model bisa diakses & relasinya berfungsi di tinker.

---

## Fase 3 — Alur Pengajuan (sisi Pegawai/User)

### 3.1 Service kandidat Atasan Langsung (skema terbuka)
- [x] Prompt:
  ```
  Buat method di MasterPegawai (atau service baru
  app/Services/AtasanLangsungService.php) untuk mengambil daftar kandidat
  Atasan Langsung bagi seorang pemohon: pegawai dengan opd_id sama dengan
  pemohon, jenis_jabatan = 'Jabatan Struktural', id != id pemohon, dan
  nip TIDAK ADA di daftar tb_opd_aktif.nip_kepala manapun (mengecualikan
  Kepala OPD -- PENTING: jangan pakai kolom tb_opd_aktif.kepala_pegawai_id,
  FK itu rusak/selalu null, lihat catatan di requirement.md bagian 5.4).
  Method: kandidatAtasanLangsung(MasterPegawai $pemohon): Collection.
  Tambahkan juga filter opsional: hanya pegawai yang users-nya sudah
  tertaut (punya akun login lewat pegawai_id, hasil Fase 0.1) supaya
  pemohon tidak memilih kandidat yang belum bisa login untuk approve.
  ```

### 3.2 Service validasi kelayakan & sisa cuti
- [x] Prompt:
  ```
  Buat service class app/Services/CutiValidationService.php yang
  memvalidasi kelayakan pengajuan cuti sesuai requirement.md bagian 4 dan
  ketentuan-cuti.md, untuk satu MasterPegawai + JenisCutiAturan + rentang
  tanggal:
  - cek syarat_masa_kerja_bulan (perlu tanggal mulai kerja pegawai --
    identifikasi dulu kolom mana di tb_pegawai_aktif yang bisa dipakai
    sebagai tanggal mulai kerja, jika belum ada catat sebagai temuan)
  - cek tidak ada cuti_pengajuan lain (status diajukan/disetujui) milik
    pegawai yang sama dengan rentang tanggal tumpang tindih
  - cek plafon/maks_hari sesuai jenis (untuk Cuti Tahunan pakai
    cuti_saldo_tahunan.sisa; untuk jenis lain hitung akumulasi dari
    riwayat cuti_pengajuan berstatus disetujui pada periode berjalan)
  - cek aturan silang: sedang Cuti Besar tahun ini -> tolak pengajuan Cuti
    Tahunan tahun sama; sedang CLTN -> tolak semua jenis cuti lain
  Method utama: validate(MasterPegawai $pegawai, JenisCutiAturan $jenis,
  Carbon $mulai, ?Carbon $selesai): array (list pesan error, kosong jika
  lolos). Sertakan unit test dasar.
  ```

### 3.3 Controller & form Ajukan Cuti
- [x] Prompt:
  ```
  Buat CutiPengajuanController@create dan @store di
  app/Http/Controllers/Cuti/. Form ajukan cuti
  (resources/views/cuti-baru/ajukan.blade.php, gaya modern sama seperti
  halaman lain -- card rounded-2xl, tema sky-blue): pilih jenis cuti
  (dropdown dari jenis_cuti_aturan aktif, tampilkan keterangan aturan
  singkat saat dipilih), pilih Atasan Langsung (dropdown/pencarian ketik
  dari AtasanLangsungService::kandidatAtasanLangsung() hasil langkah 3.1
  -- kalau kosong tampilkan pesan "Tidak ada pejabat struktural di OPD
  Anda, jenjang ini akan dilewati" dan izinkan submit tanpa pilihan),
  tanggal mulai & selesai, alasan, alamat & telepon selama cuti, upload
  lampiran (jika perlu_dokumen), khusus Cuti Melahirkan tambahkan field
  keterangan_anak_ke. Saat submit: jalankan CutiValidationService, jika
  gagal kembalikan error, jika lolos: buat nomor_pengajuan (format
  sementara CT-{tahun}-{urutan 6 digit}), hitung lama_hari, simpan
  cuti_pengajuan dengan status 'diajukan' dan atasan_langsung_pegawai_id
  dari pilihan, hitung jumlah_tahap dari
  jenis_cuti_aturan.butuh_persetujuan_admin (2 atau 3, dikurangi 1 lagi
  kalau tidak ada Atasan Langsung dipilih), generate qr_token
  (Str::uuid()), lalu buat baris cuti_pengajuan_tahap otomatis: urutan 1
  atasan_langsung (status 'menunggu' jika ada kandidat dipilih, atau
  langsung 'dilewati' jika tidak ada), urutan 2 kepala_unit_kerja (status
  'menunggu'), urutan 3 admin jika berlaku (status 'menunggu'). Simpan
  lampiran ke cuti_pengajuan_lampiran & disk public/cuti-lampiran. Ambil
  pegawai dari Auth::user()->pegawai (hasil Fase 0.1).
  ```

### 3.4 Halaman Riwayat Cuti Saya
- [x] Prompt:
  ```
  Buat CutiPengajuanController@myIndex + view
  resources/views/cuti-baru/riwayat.blade.php: daftar pengajuan milik
  pegawai yang login (paginate 10), kartu ringkasan sisa cuti tahunan
  (dari cuti_saldo_tahunan tahun berjalan), kolom status per baris
  (badge warna beda per status), progres jenjang saat ini (mis. "Menunggu
  Atasan Langsung" / "Menunggu Kepala Unit Kerja" / "Menunggu Admin" /
  "Disetujui" / "Ditolak"), tombol lihat detail, dan tombol batalkan untuk
  pengajuan yang masih ada tahap 'menunggu'.
  ```

### 3.5 Aksi batalkan pengajuan
- [x] Prompt:
  ```
  Tambahkan CutiPengajuanController@cancel (route POST
  /cuti-baru/{cutiPengajuan}/batalkan). Hanya boleh dilakukan pemilik
  pengajuan (pegawai_id cocok dengan Auth::user()->pegawai_id), dan hanya
  jika status masih 'diajukan' (belum ada tahap yang diputuskan selain
  'menunggu'). Set status jadi 'dibatalkan', catat di
  cuti_pengajuan_log, tandai semua baris cuti_pengajuan_tahap yang masih
  'menunggu' jadi 'dilewati'.
  ```
- **Kriteria Fase 3 selesai**: pegawai bisa mengajukan cuti lewat form,
  validasi otomatis menolak pengajuan yang melanggar aturan, riwayat +
  status tampil dengan benar, bisa membatalkan sebelum diproses.

---

## Fase 4 — Alur Persetujuan Berjenjang

### 4.1 Policy otorisasi approval
- [x] Prompt:
  ```
  Buat app/Policies/CutiPengajuanTahapPolicy.php dengan method approve(User
  $user, CutiPengajuanTahap $tahap) sesuai logika di requirement.md
  bagian 9: admin selalu boleh; untuk jenjang atasan_langsung user harus
  terhubung (lewat users.pegawai_id) ke pegawai yang tercatat sebagai
  cuti_pengajuan.atasan_langsung_pegawai_id pada pengajuan terkait (pilihan
  pemohon saat submit, dari kandidat hasil langkah 3.1 -- bukan relasi
  tetap per pegawai); untuk jenjang kepala_unit_kerja user harus role
  'opd' dan OPD induk pemohon ada di opdDiampu(); untuk jenjang admin user
  harus role 'admin'. Tambahkan juga aturan: $tahap harus jadi baris
  urutan terkecil berstatus 'menunggu' pada cuti_pengajuan terkait (tidak
  bisa approve out of order). Daftarkan policy di AuthServiceProvider atau
  bootstrap/app.php sesuai konvensi Laravel 12 di proyek ini.
  ```

### 4.2 Service inti "putuskan tahap" (dipakai bersama oleh 2 modul)
- [x] Prompt:
  ```
  Buat app/Services/CutiTahapKeputusanService.php dengan 2 method:
  setujui(CutiPengajuanTahap $tahap, User $penyetuju, ?string $catatan) dan
  tolak(CutiPengajuanTahap $tahap, User $penyetuju, string $catatan).
  setujui(): set status baris ini 'disetujui', penyetuju_id,
  diputuskan_pada; jika ini baris urutan terakhir (urutan ===
  jumlah_tahap pada cuti_pengajuan induk), set cuti_pengajuan.status jadi
  'disetujui', catat di cuti_pengajuan_log, panggil update saldo (lihat
  4.4 nanti) + generate dokumen (lihat Fase 5) -- kalau bukan baris
  terakhir, biarkan status cuti_pengajuan tetap 'diajukan' (tahap
  berikutnya otomatis jadi giliran aktif). tolak(): set status baris ini
  'ditolak' + catatan wajib, set semua baris tahap lain yang masih
  'menunggu' jadi 'dilewati', set cuti_pengajuan.status jadi 'ditolak',
  catat di log. Service ini akan dipanggil dari DUA controller terpisah
  (langkah 4.3 dan 4.5) -- taruh semua logika transisi status di sini
  supaya tidak terduplikasi.
  ```

### 4.3 Halaman "Persetujuan Saya" (inbox jenjang 1 & 2)
- [x] Prompt:
  ```
  Buat CutiPengajuanController@persetujuan + @setujui + @tolak (route POST
  /cuti-baru/persetujuan/{cutiPengajuanTahap}/setujui dan /tolak, memanggil
  CutiTahapKeputusanService dari 4.2) + view
  resources/views/cuti-baru/persetujuan.blade.php. HANYA menampilkan baris
  cuti_pengajuan_tahap berstatus 'menunggu' dengan jenjang IN
  ('atasan_langsung','kepala_unit_kerja') -- jenjang 'admin' TIDAK muncul
  di sini sama sekali, itu wewenang modul terpisah di langkah 4.5. Query
  yang $request->user() berhak approve menurut CutiPengajuanTahapPolicy
  dari 4.1 (role admin -> semua baris jenjang 1/2 yang menunggu; role opd
  -> baris jenjang kepala_unit_kerja yang OPD pemohonnya ada di
  opdDiampu(); siapa pun yang tertaut users.pegawai_id ke
  cuti_pengajuan.atasan_langsung_pegawai_id pengajuan terkait -> baris
  jenjang atasan_langsung pengajuan itu). Tampilkan nama pegawai, jenis
  cuti, tanggal, jenjang saat ini, tombol Setujui/Tolak (modal alasan
  untuk tolak, pola modal Alpine yang sudah dipakai di Master Pegawai).
  Paginate 10.
  ```

### 4.4 Update saldo cuti tahunan saat disetujui
- [x] Prompt:
  ```
  Buat CutiSaldoService::kurangi(MasterPegawai $pegawai, int $tahun, int
  $hari) dan pastikan(MasterPegawai $pegawai, int $tahun) (buat/ambil
  baris cuti_saldo_tahunan, default jatah 12, carry_over dihitung dari
  sisa tahun sebelumnya maks 6). Panggil kurangi() dari
  CutiTahapKeputusanService::setujui() (langkah 4.2) saat pengajuan Cuti
  Tahunan mencapai status akhir 'disetujui', lalu sinkronkan hasilnya ke
  kolom cache tb_pegawai_aktif.sisa_cuti_tahunan.
  ```

### 4.5 Modul terpisah: Persetujuan Akhir Cuti Besar & CLTN
- [x] Prompt:
  ```
  Buat modul admin-only yang BERDIRI SENDIRI (bukan bagian dari
  CutiPengajuanController/halaman Persetujuan Saya) untuk persetujuan
  akhir Cuti Besar & CLTN, sesuai requirement.md bagian 1, 8, dan 9:
  - Controller baru app/Http/Controllers/Cuti/PersetujuanAkhirCutiController.php
    dengan method index(), setujui(), tolak() -- setujui()/tolak() cukup
    memanggil CutiTahapKeputusanService dari langkah 4.2, jangan duplikasi
    logikanya.
  - Route baru, grup middleware ['auth','admin'] (BUKAN
    CutiPengajuanTahapPolicy -- akses modul ini cukup dicek lewat
    middleware admin biasa): GET /cuti-baru/persetujuan-akhir (index),
    POST /cuti-baru/persetujuan-akhir/{cutiPengajuanTahap}/setujui, POST
    .../tolak.
  - index() HANYA mengambil baris cuti_pengajuan_tahap dengan jenjang =
    'admin' DAN status = 'menunggu' DAN pastikan tahap urutan 1 & 2 pada
    cuti_pengajuan induk sudah berstatus 'disetujui' (validasi ganda,
    jangan asumsikan urutan tahap otomatis benar).
  - View baru resources/views/cuti-baru/persetujuan-akhir.blade.php, gaya
    modern sky-blue senada halaman lain, tapi tampilkan LEBIH detail dari
    Persetujuan Saya: untuk tiap pengajuan, tampilkan juga riwayat
    keputusan jenjang 1 (nama atasan langsung, tanggal, catatan) dan
    jenjang 2 (nama kepala unit kerja, tanggal, catatan) supaya Admin
    punya konteks penuh sebelum memutuskan jenjang akhir. Paginate 10.
  - Tambahkan CutiPengajuanTahapPolicy::approve() tetap dipertahankan
    untuk jenjang admin (dipakai kalau suatu saat modul lain perlu cek
    otorisasi yang sama), tapi controller modul ini boleh langsung
    mengandalkan middleware admin tanpa manggil policy tsb.
  ```
- **Kriteria Fase 4 selesai**: alur setuju/tolak berjalan sesuai jenjang
  di requirement.md bagian 3 untuk 2 jenjang (cuti biasa) maupun 3 jenjang
  (Besar/CLTN); orang yang tidak berwenang tidak bisa approve; saldo cuti
  tahunan berkurang otomatis setelah disetujui final; **Persetujuan Akhir
  Cuti Besar & CLTN tampil sebagai modul terpisah dari Persetujuan Saya**,
  dan hanya menampilkan pengajuan yang sudah disetujui di jenjang 1 & 2.

---

## Fase 5 — Dokumen PDF & QR Code

### 5.1 Pasang dependency
- [ ] Prompt:
  ```
  Pasang composer package barryvdh/laravel-dompdf dan
  simplesoftwareio/simple-qrcode ke proyek ini (composer require),
  publish config jika diperlukan, pastikan tidak konflik dependency yang
  ada.
  ```

### 5.2 Template PDF surat cuti
> **MENUNGGU TEMPLATE RESMI** — belum dikerjakan. Template formulir cuti
> (tata letak/desain visual resmi) akan disiapkan menyusul dan diberikan
> terpisah. Jangan mulai langkah ini sampai template tsb tersedia; Fase
> 5.1, 5.3, 5.4, 5.5 tidak bergantung pada langkah ini dan boleh tetap
> dikerjakan lebih dulu (5.3 bisa dibangun dengan view placeholder
> sederhana dulu, lalu tinggal diganti isinya begitu template resmi ada).
- [ ] Prompt (baru dijalankan setelah template resmi diterima):
  ```
  Buat view Blade khusus PDF resources/views/cuti-baru/pdf/surat-cuti.blade.php
  mengikuti [template resmi yang diberikan -- lampirkan saat menjalankan
  prompt ini], kemungkinan besar berbasis format Lampiran II Perban BKN
  No. 7/2021 (permintaan, pertimbangan, keputusan cuti) + kop surat
  Pemerintah Kabupaten Klaten (logo-klaten.png, sama seperti kop di
  halaman Rekapitulasi). Isi minimal yang wajib ada: nomor_sk, identitas
  pegawai (nama, NIP, jabatan, unit kerja dari relasi opd), jenis cuti,
  tanggal mulai-selesai, lama_hari, alasan, alamat/telepon selama cuti,
  riwayat semua baris cuti_pengajuan_tahap (nama & jabatan tiap penyetuju
  + tanggal keputusan), dan gambar QR code di pojok kanan bawah yang
  meng-encode URL route('verifikasi-cuti', $cutiPengajuan->qr_token).
  ```

### 5.3 Generate & simpan PDF saat disetujui final
- [ ] Prompt:
  ```
  Buat CutiDokumenService::generate(CutiPengajuan $pengajuan): string yang
  merender sebuah view ke PDF (Barryvdh\DomPDF\Facade\Pdf) dan simpan ke
  storage/app/public/cuti-pengajuan/{nomor_pengajuan}.pdf, update
  cuti_pengajuan.pdf_path dan pdf_generated_at. Kalau template resmi dari
  langkah 5.2 belum tersedia, pakai view placeholder sederhana dulu
  (resources/views/cuti-baru/pdf/surat-cuti-placeholder.blade.php, isi
  data minimal yang sama seperti disebut di 5.2 tanpa styling khusus) --
  gampang diganti ke view resmi nanti tanpa mengubah service ini. Panggil
  service ini dari CutiTahapKeputusanService::setujui() (langkah 4.2)
  tepat setelah status cuti_pengajuan jadi 'disetujui'.
  ```

### 5.4 Halaman verifikasi QR publik
- [ ] Prompt:
  ```
  Buat route GET /verifikasi-cuti/{token} TANPA middleware auth (publik),
  controller VerifikasiCutiController@show, view
  resources/views/cuti-baru/verifikasi.blade.php (halaman mandiri, bukan
  pakai x-app-layout karena publik -- gaya senada halaman login/welcome).
  Cari cuti_pengajuan berdasar qr_token; jika tidak ketemu atau status
  bukan 'disetujui', tampilkan pesan "Dokumen tidak ditemukan/tidak
  valid". Jika ketemu, tampilkan read-only: nama, NIP, jenis cuti,
  tanggal, nomor_sk, status "Sah/Terverifikasi", nama & jabatan
  penyetuju tahap terakhir. JANGAN tampilkan alasan/alamat/telepon
  (data sensitif).
  ```

### 5.5 Tombol cetak/unduh di halaman detail pengajuan
- [ ] Prompt:
  ```
  Buat CutiPengajuanController@show + view
  resources/views/cuti-baru/show.blade.php: detail satu pengajuan
  (identitas pegawai, jenis cuti, tanggal, alasan, lampiran, dan daftar
  cuti_pengajuan_tahap dengan status masing-masing). Kalau status
  'disetujui' dan pdf_path ada, tampilkan tombol "Unduh PDF" (link ke
  Storage::url($pengajuan->pdf_path)). Akses dibatasi: pemilik pengajuan,
  siapa pun yang jadi penyetuju salah satu tahapnya, atau admin.
  ```
- **Kriteria Fase 5 selesai**: pengajuan yang disetujui menghasilkan file
  PDF resmi dengan QR code, QR bisa dipindai dan menampilkan halaman
  verifikasi publik yang benar.

---

## Fase 6 — Sidebar & Navigasi

### 6.1 Rombak grup "Cuti Baru"
- [x] Prompt:
  ```
  Update resources/views/layouts/sidebar.blade.php: ganti 4 placeholder
  Menu 1-4 di grup "Cuti Baru" dengan menu nyata sesuai requirement.md
  bagian 8: "Ajukan Cuti" (route cuti-baru.ajukan, semua role), "Riwayat
  Cuti Saya" (cuti-baru.riwayat, semua role), "Persetujuan Saya"
  (cuti-baru.persetujuan, semua role -- halamannya sendiri yang
  menyaring isi jenjang 1 & 2 sesuai wewenang), "Persetujuan Akhir Cuti
  Besar & CLTN" (cuti-baru.persetujuan-akhir, admin only -- modul
  terpisah dari langkah 4.5, beri item admin => true seperti pola menu
  admin-only lain di sidebar). "Master Aturan Cuti" TIDAK masuk grup ini
  -- itu masuk grup "Setting Master" (sudah dikerjakan di langkah 1.3).
  Hapus route placeholder lama (cuti-baru.menu1-4) dari routes/web.php
  beserta view placeholder terkait jika tidak dipakai lagi di tempat lain.
  ```
- **Kriteria Fase 6 selesai**: menu sidebar "Cuti Baru" mengarah ke
  halaman fungsional, bukan placeholder lagi, dan modul "Persetujuan
  Akhir Cuti Besar & CLTN" tampil terpisah dari "Persetujuan Saya" serta
  hanya terlihat oleh Admin.

---

## Fase 7 — Pengujian End-to-End

### 7.1 Uji alur 2 jenjang (cuti biasa)
- [ ] Prompt:
  ```
  Jalankan uji end-to-end manual (lewat server sementara + curl, seperti
  pola verifikasi yang dipakai di seluruh sesi ini) untuk alur Cuti
  Tahunan: pegawai ajukan -> muncul di Persetujuan Saya milik atasan
  langsungnya -> setujui -> muncul di Persetujuan Saya akun opd yang
  mengampu OPD pemohon -> setujui -> status jadi disetujui, saldo
  cuti_saldo_tahunan berkurang, PDF+QR ter-generate, halaman verifikasi
  menampilkan data benar. Uji juga jalur tolak di masing-masing jenjang
  (harus langsung berhenti, tidak lanjut ke jenjang berikutnya).
  ```

### 7.2 Uji alur 3 jenjang (Cuti Besar / CLTN) & modul terpisah
- [ ] Prompt:
  ```
  Ulangi uji 7.1 untuk Cuti Besar dan CLTN, dengan penekanan khusus pada
  pemisahan modul: (a) pengajuan yang baru disetujui jenjang 1 & 2 TIDAK
  boleh muncul di halaman Persetujuan Saya sama sekali (sudah selesai di
  sana), dan HARUS muncul di /cuti-baru/persetujuan-akhir; (b) sebelum
  jenjang 1 & 2 selesai, pengajuan itu juga TIDAK BOLEH muncul di
  /cuti-baru/persetujuan-akhir (memverifikasi validasi ganda di langkah
  4.5); (c) akun role 'opd' dan role 'user' mendapat 403 saat mengakses
  /cuti-baru/persetujuan-akhir langsung lewat URL; (d) halaman
  persetujuan-akhir menampilkan riwayat keputusan jenjang 1 & 2 dengan
  benar. Uji juga validasi silang: pegawai yang sedang Cuti Besar tidak
  bisa mengajukan Cuti Tahunan tahun yang sama; pegawai yang sedang CLTN
  tidak bisa mengajukan jenis cuti lain.
  ```

### 7.3 Uji validasi & kasus tepi
- [ ] Prompt:
  ```
  Uji kasus tepi: pengajuan dengan tanggal tumpang tindih pengajuan lain
  ditolak validasi; pegawai dengan masa kerja kurang dari syarat ditolak
  validasi; pembatalan pengajuan oleh pegawai sebelum diproses berhasil
  dan menghentikan alur; OPD tanpa kandidat Atasan Langsung (semua
  pegawainya Jabatan Fungsional/Pelaksana selain Kepala OPD) membuat
  jenjang 1 otomatis 'dilewati' sesuai requirement.md poin 9; kandidat
  Atasan Langsung yang belum tertaut akun login tidak muncul di daftar
  pilihan form pengajuan.
  ```

---

## Ringkasan Urutan Fase

```
Fase 0 (Prasyarat data)
   │
   ▼
Fase 1 (Master Aturan Cuti) ──► Fase 2 (Skema DB Pengajuan)
   │                                      │
   └──────────────┬───────────────────────┘
                   ▼
        Fase 3 (Alur Pengajuan/User)
                   │
                   ▼
        Fase 4 (Alur Persetujuan Berjenjang)
                   │
                   ▼
        Fase 5 (PDF & QR)
                   │
                   ▼
        Fase 6 (Sidebar & Navigasi)
                   │
                   ▼
        Fase 7 (Pengujian End-to-End)
```
