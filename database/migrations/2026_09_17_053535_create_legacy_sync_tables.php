<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel-tabel mirror hasil sinkronisasi dari SIMABSARA2017 (via menu
     * "Sync Data") beserta infrastruktur sync-nya (sync_logs, sync_runs).
     * Digabung dari 12 migration terpisah yang tadinya dibuat satu per
     * satu di hari yang sama.
     *
     * "sysdb_pns" dan "riwayatcuti" dibuat lewat SQL mentah (bukan
     * Blueprint) karena strukturnya diambil persis dari database yang
     * berjalan (SHOW CREATE TABLE) -- kedua tabel ini sebelumnya sempat
     * dapat kolom & index tambahan lewat beberapa migration ALTER lama
     * yang file-nya sudah hilang (status_aktif, manual_override_status,
     * kolom override_*, index nip_baru, index komposit tanggal+status),
     * jadi menyalin definisi asli dari database jauh lebih akurat
     * dibanding menulis ulang manual dari migration lama yang sudah usang.
     */
    public function up(): void
    {
        Schema::create('tkodecuti', function (Blueprint $table) {
            $table->id();
            $table->string('kodecuti', 2)->nullable();
            $table->string('keterangancuti', 100)->nullable();
            $table->string('kodesapa', 50)->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique('kodecuti');
            $table->comment('Sumber: SIMABSARA2017, tabel TKodeCuti');
        });

        Schema::create('sysdb_wkguni', function (Blueprint $table) {
            $table->id();
            $table->string('wku_inscod', 2)->nullable();
            $table->string('wku_wrkcod', 64)->nullable();
            $table->string('wku_name', 40)->nullable();
            $table->string('status', 1)->nullable();
            $table->string('wku_ukerpjg', 244)->nullable();
            $table->string('wku_lama', 64)->nullable();
            $table->longText('wku_alamat')->nullable();
            $table->string('wku_telepon', 20)->nullable();
            $table->string('sapk_wkguni', 50)->nullable();
            $table->string('lokasi1', 30)->nullable();
            $table->string('lokasi2', 30)->nullable();
            $table->string('jabatanidcard', 35)->nullable();
            $table->string('id_unor', 100)->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique('wku_wrkcod');
            $table->comment('Sumber: SIMABSARA2017, tabel SYSDB_WKGUNI');
        });

        Schema::create('sysdb_wkgunitampungan', function (Blueprint $table) {
            $table->id();
            $table->string('wku_inscod', 2)->nullable();
            $table->string('wku_wrkcod', 64)->nullable();
            $table->string('wku_name', 40)->nullable();
            $table->string('status', 1)->nullable();
            $table->string('wku_ukerpjg', 244)->nullable();
            $table->string('wku_lama', 64)->nullable();
            $table->longText('wku_alamat')->nullable();
            $table->string('wku_telepon', 20)->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique('wku_wrkcod');
            $table->comment('Sumber: SIMABSARA2017, tabel SYSDB_WKGUNITAMPUNGAN');
        });

        Schema::create('tprofilunitkerja', function (Blueprint $table) {
            $table->id();
            $table->string('kodeuker', 50)->nullable();
            $table->string('eselon', 50)->nullable();
            $table->string('kodeinstansigaji', 50)->nullable();
            $table->string('nipkepala', 50)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('indeks', 50)->nullable();
            $table->string('kodestatusdata', 50)->nullable();
            $table->string('isvisible', 1)->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique('kodeuker');
            $table->comment('Sumber: SIMABSARA2017, tabel TProfilUnitKerja');
        });

        Schema::create('sysdb_pnsdetail', function (Blueprint $table) {
            $table->id();
            $table->string('dtl_pnsnip', 9)->nullable();
            $table->string('dtl_pnstlp', 30)->nullable();
            $table->string('dtl_pnsfax', 15)->nullable();
            $table->string('dtl_pnsemail', 60)->nullable();
            $table->string('dtl_addrs', 60)->nullable();
            $table->string('dtl_poscod', 5)->nullable();
            $table->string('dtl_adrloc', 8)->nullable();
            $table->string('dtl_pnsnot', 255)->nullable();
            $table->dateTime('dtl_jargjtgl')->nullable();
            $table->string('dukuh_jalan', 50)->nullable()->comment('kolom sumber: dukuh/jalan');
            $table->string('desa', 50)->nullable();
            $table->string('kecamatan', 50)->nullable();
            $table->string('kabupaten', 50)->nullable();
            $table->string('provinsi', 50)->nullable();
            $table->string('status_data', 2)->nullable();
            $table->string('useruker', 100)->nullable();
            $table->dateTime('updtuker')->nullable();
            $table->string('dtl_pnsemailgoid', 50)->nullable();
            $table->string('dtl_addrs200', 200)->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique('dtl_pnsnip');
            $table->comment('Sumber: SIMABSARA2017, tabel SYSDB_PNSDETAIL');
        });

        Schema::create('sysdb_pnskpg', function (Blueprint $table) {
            $table->id();
            $table->string('kpg_pnsnip', 9)->nullable();
            $table->string('kpg_payroll', 15)->nullable();
            $table->string('kpg_rekkor', 15)->nullable();
            $table->string('kpg_karkre', 16)->nullable();
            $table->string('kpg_asrnsi', 15)->nullable();
            $table->decimal('kpg_jnasrn', 1, 0)->nullable();
            $table->decimal('kpg_kdebit', 1, 0)->nullable();
            $table->string('kpg_tasnum', 15)->nullable();
            $table->string('kpg_asknum', 15)->nullable();
            $table->string('kpg_farmsts', 1)->nullable();
            $table->string('kpg_npwnum', 15)->nullable();
            $table->string('kpg_nik', 16)->nullable();
            $table->string('kpg_kk', 16)->nullable();
            $table->string('iduser', 50)->nullable();
            $table->string('modidatetime', 50)->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique('kpg_pnsnip');
            $table->comment('Sumber: SIMABSARA2017, tabel SYSDB_PNSKPG');
        });

        DB::unprepared(<<<'SQL'
            CREATE TABLE `sysdb_pns` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `pns_pnsnip` varchar(9) DEFAULT NULL,
              `pns_pnsnam` varchar(40) DEFAULT NULL,
              `status_aktif` enum('Aktif','Tidak Aktif') NOT NULL DEFAULT 'Tidak Aktif' COMMENT 'Ditentukan dengan mencocokkan nip_baru terhadap tb_pegawai_dump (daftar pegawai aktif)',
              `manual_override_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'true = status_aktif diatur manual (nonaktifkan/aktifkan), dilewati saat sinkronisasi ulang',
              `pns_ftitle` varchar(15) DEFAULT NULL,
              `pns_pnspob` varchar(4) DEFAULT NULL,
              `pns_pnsdob` varchar(8) DEFAULT NULL,
              `pns_sexcod` varchar(1) DEFAULT NULL,
              `pns_rlgcod` varchar(1) DEFAULT NULL,
              `pns_scicod` varchar(7) DEFAULT NULL,
              `pns_strnco` varchar(2) DEFAULT NULL,
              `pns_asefdt` varchar(8) DEFAULT NULL,
              `pns_ccsefd` datetime DEFAULT NULL,
              `pns_kndpns` varchar(2) DEFAULT NULL,
              `pns_srcdta` varchar(1) DEFAULT NULL,
              `pns_basrec` varchar(1) DEFAULT NULL,
              `pns_csefdt` varchar(8) DEFAULT NULL,
              `pns_stapns` varchar(1) DEFAULT NULL,
              `pns_oatsta` varchar(1) DEFAULT NULL,
              `pns_kpgsrn` varchar(11) DEFAULT NULL,
              `pns_mincod` varchar(2) DEFAULT NULL,
              `pns_inscod` varchar(2) DEFAULT NULL,
              `override_opd_kode` varchar(2) DEFAULT NULL COMMENT 'Hasil mutasi manual, menimpa pns_inscod saat ditampilkan',
              `pns_wkucod` varchar(10) DEFAULT NULL,
              `override_unit_kerja_kode` varchar(64) DEFAULT NULL COMMENT 'Hasil mutasi manual, menimpa pns_wkucod saat ditampilkan',
              `override_unit_kerja_nama` varchar(255) DEFAULT NULL COMMENT 'Nama unit kerja tujuan mutasi, dipakai jika tidak match ke sysdb_wkguni',
              `pns_genpos` varchar(2) DEFAULT NULL,
              `pns_echcod` varchar(2) DEFAULT NULL,
              `pns_inadte` datetime DEFAULT NULL,
              `pns_echefd` datetime DEFAULT NULL,
              `pns_strtng` varchar(1) DEFAULT NULL,
              `pns_ftrcod` varchar(3) DEFAULT NULL,
              `pns_cfpcod` varchar(5) DEFAULT NULL,
              `pns_maincp` decimal(6,3) DEFAULT NULL,
              `pns_suppcp` decimal(6,3) DEFAULT NULL,
              `pns_stlud` varchar(1) DEFAULT NULL,
              `pns_kndsal` varchar(1) DEFAULT NULL,
              `pns_crncod` varchar(2) DEFAULT NULL,
              `pns_crndte` datetime DEFAULT NULL,
              `pns_crnsta` varchar(1) DEFAULT NULL,
              `pns_wprank` decimal(4,2) DEFAULT NULL,
              `pns_lrncod` varchar(1) DEFAULT NULL,
              `pns_pofcod` varchar(5) DEFAULT NULL,
              `pns_ktuaco` varchar(2) DEFAULT NULL,
              `pns_wklcod` varchar(8) DEFAULT NULL,
              `pns_marsta` varchar(1) DEFAULT NULL,
              `pns_dpcwfe` decimal(1,0) DEFAULT NULL,
              `pns_dpcchl` decimal(2,0) DEFAULT NULL,
              `pns_awrsta` varchar(1) DEFAULT NULL,
              `pns_pnlcod` varchar(1) DEFAULT NULL,
              `pns_stepns` varchar(2) DEFAULT NULL,
              `pns_pospso` varchar(2) DEFAULT NULL,
              `pns_proorg` varchar(1) DEFAULT NULL,
              `pns_solorg` varchar(1) DEFAULT NULL,
              `pns_golkar` varchar(1) DEFAULT NULL,
              `pns_mngloc` varchar(1) DEFAULT NULL,
              `pns_reasta` varchar(1) DEFAULT NULL,
              `pns_penefd` datetime DEFAULT NULL,
              `pns_repnam` varchar(40) DEFAULT NULL,
              `pns_creaby` varchar(8) DEFAULT NULL,
              `pns_creadt` datetime DEFAULT NULL,
              `pns_updtby` varchar(8) DEFAULT NULL,
              `pns_updtdt` datetime DEFAULT NULL,
              `pns_btchid` varchar(12) DEFAULT NULL,
              `pns_postdt` datetime DEFAULT NULL,
              `pns_tercod` varchar(2) DEFAULT NULL,
              `pns_tasnum` varchar(15) DEFAULT NULL,
              `pns_asknum` varchar(15) DEFAULT NULL,
              `pns_korcod` varchar(1) DEFAULT NULL,
              `pns_famsts` varchar(1) DEFAULT NULL,
              `pns_npwnum` varchar(15) DEFAULT NULL,
              `pns_niknum` varchar(17) DEFAULT NULL,
              `pns_pnsadr` varchar(50) DEFAULT NULL,
              `pns_poscod` varchar(5) DEFAULT NULL,
              `pns_adrloc` varchar(8) DEFAULT NULL,
              `pns_lahirloc` varchar(60) DEFAULT NULL,
              `pns_unkerloc` varchar(60) DEFAULT NULL,
              `pns_tgllstr` datetime DEFAULT NULL,
              `pns_jmjmstr` varchar(4) DEFAULT NULL,
              `pns_tgllfun` datetime DEFAULT NULL,
              `pns_jmjmfun` varchar(4) DEFAULT NULL,
              `pns_thllpdk` varchar(4) DEFAULT NULL,
              `pns_jargjcd` varchar(3) DEFAULT NULL,
              `pns_tmtpen` varchar(9) DEFAULT NULL,
              `pns_proses` datetime DEFAULT NULL,
              `pns_tmtgaji` datetime DEFAULT NULL,
              `pns_ketgaji` varchar(200) DEFAULT NULL,
              `pns_jabtam` varchar(2) DEFAULT NULL,
              `pns_tmttam` datetime DEFAULT NULL,
              `pns_kodgr2` varchar(2) DEFAULT NULL,
              `pns_pmkth` decimal(2,0) DEFAULT NULL,
              `pns_pmkbl` decimal(2,0) DEFAULT NULL,
              `kode_formasi` varchar(7) DEFAULT NULL,
              `nomor_sk_tugas` varchar(30) DEFAULT NULL,
              `tgl_sk_tugas` datetime DEFAULT NULL,
              `kd_pjb` varchar(2) DEFAULT NULL,
              `nip_baru` varchar(18) DEFAULT NULL,
              `unit_lama` varchar(10) DEFAULT NULL,
              `pns_thnlls` decimal(4,0) DEFAULT NULL,
              `pns_kkrsno1` varchar(15) DEFAULT NULL,
              `pns_nikno` varchar(16) DEFAULT NULL,
              `pns_jabattam` varchar(10) DEFAULT NULL,
              `pns_rtitle` varchar(25) DEFAULT NULL,
              `pns_kksrno` varchar(20) DEFAULT NULL,
              `status_data` varchar(2) DEFAULT NULL,
              `useruker` varchar(100) DEFAULT NULL,
              `updtuker` datetime DEFAULT NULL,
              `idpnsbkn` varchar(50) DEFAULT NULL,
              `eselonbkn` varchar(2) DEFAULT NULL,
              `pns_kodetpp` varchar(10) DEFAULT NULL,
              `pns_stepnstpp` varchar(5) DEFAULT NULL,
              `pns_crncodtpp` varchar(3) DEFAULT NULL,
              `pns_jabatantpp` varchar(10) DEFAULT NULL,
              `pns_unitkerjatpp` varchar(10) DEFAULT NULL,
              `pns_istpp` varchar(1) DEFAULT NULL,
              `pns_issae` varchar(1) DEFAULT NULL,
              `pns_isinspektorat` varchar(1) DEFAULT NULL,
              `pns_ishukdis` varchar(1) DEFAULT NULL,
              `pns_iskinerja` varchar(1) DEFAULT NULL,
              `synced_at` timestamp NULL DEFAULT NULL,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `sysdb_pns_pns_pnsnip_unique` (`pns_pnsnip`),
              KEY `sysdb_pns_nip_baru_index` (`nip_baru`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sumber: SIMABSARA2017, tabel SYSDB_PNS'
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TABLE `riwayatcuti` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `nip` varchar(18) DEFAULT NULL,
              `idcltn` varchar(255) DEFAULT NULL,
              `jeniscuti` varchar(2) DEFAULT NULL,
              `nomorskcltn` varchar(255) DEFAULT NULL,
              `tanggalskcltn` datetime DEFAULT NULL,
              `tanggalawalcltn` date DEFAULT NULL,
              `tanggalakhircltn` date DEFAULT NULL,
              `tanggalaktifcltn` datetime DEFAULT NULL,
              `nobkn` varchar(255) DEFAULT NULL,
              `tanggalbkn` datetime DEFAULT NULL,
              `iduser` varchar(50) DEFAULT NULL,
              `isvisible` varchar(1) DEFAULT NULL,
              `modidatetime` varchar(50) DEFAULT NULL,
              `lamahari` int(11) DEFAULT NULL,
              `kodesatuancuti` varchar(2) DEFAULT NULL,
              `gelardepan` varchar(10) DEFAULT NULL,
              `gelarbelakang` varchar(10) DEFAULT NULL,
              `jabatan` varchar(10) DEFAULT NULL,
              `unitkerja` varchar(10) DEFAULT NULL,
              `masakerja` varchar(5) DEFAULT NULL,
              `alasancuti` varchar(200) DEFAULT NULL,
              `catatancuti` varchar(200) DEFAULT NULL,
              `n2` int(11) DEFAULT NULL,
              `n2ket` varchar(200) DEFAULT NULL,
              `n1` int(11) DEFAULT NULL,
              `n1ket` varchar(200) DEFAULT NULL,
              `n` int(11) DEFAULT NULL,
              `nket` varchar(200) DEFAULT NULL,
              `besar` int(11) DEFAULT NULL,
              `sakit` int(11) DEFAULT NULL,
              `melahirkan` int(11) DEFAULT NULL,
              `penting` int(11) DEFAULT NULL,
              `cltn` int(11) DEFAULT NULL,
              `alamatcuti` varchar(200) DEFAULT NULL,
              `telpon` varchar(20) DEFAULT NULL,
              `ansungsetuju` int(11) DEFAULT NULL,
              `ansungrubah` int(11) DEFAULT NULL,
              `ansungrubahalasan` varchar(180) DEFAULT NULL,
              `ansungtangguh` int(11) DEFAULT NULL,
              `ansungtangguhalasan` varchar(10) DEFAULT NULL,
              `ansungtidak` int(11) DEFAULT NULL,
              `ansungtidakalasan` varchar(180) DEFAULT NULL,
              `nipansung` varchar(18) DEFAULT NULL,
              `pybsetuju` int(11) DEFAULT NULL,
              `pybrubah` int(11) DEFAULT NULL,
              `pybrubahalasan` varchar(180) DEFAULT NULL,
              `pybtangguh` int(11) DEFAULT NULL,
              `pybtangguhalasan` varchar(180) DEFAULT NULL,
              `pybtidak` int(11) DEFAULT NULL,
              `pybtidakalasan` varchar(180) DEFAULT NULL,
              `nippyb` varchar(18) DEFAULT NULL,
              `tglproses` datetime DEFAULT NULL,
              `cutialasanpenting` varchar(2) DEFAULT NULL,
              `umpegtahunan` int(11) DEFAULT NULL,
              `umpegbesar` int(11) DEFAULT NULL,
              `umpegsakit` int(11) DEFAULT NULL,
              `umpegmelahirkan` int(11) DEFAULT NULL,
              `umpegpenting` int(11) DEFAULT NULL,
              `umpegcltn` int(11) DEFAULT NULL,
              `catatanumpeg` varchar(2) DEFAULT NULL,
              `dalamluarnegeri` varchar(2) DEFAULT NULL,
              `umpegnip` varchar(18) DEFAULT NULL,
              `catatanangsung` varchar(2) DEFAULT NULL,
              `ansungjabatanlain` varchar(200) DEFAULT NULL,
              `pybatasanlain` varchar(200) DEFAULT NULL,
              `ansungjabatan` varchar(10) DEFAULT NULL,
              `ansungunitkerja` varchar(10) DEFAULT NULL,
              `ansungtambahan` varchar(150) DEFAULT NULL,
              `pybjabatan` varchar(10) DEFAULT NULL,
              `pybunitkerja` varchar(10) DEFAULT NULL,
              `pybtambahan` varchar(150) DEFAULT NULL,
              `nomorpengantar` varchar(50) DEFAULT NULL,
              `tanggalpengantar` datetime DEFAULT NULL,
              `golru` varchar(10) DEFAULT NULL,
              `idbatchluarnegeri` varchar(50) DEFAULT NULL,
              `synced_at` timestamp NULL DEFAULT NULL,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `riwayatcuti_natural_key` (`nip`,`jeniscuti`,`tanggalawalcltn`),
              KEY `riwayatcuti_tanggalawalcltn_index` (`tanggalawalcltn`),
              KEY `riwayatcuti_tanggal_status_index` (`tanggalawalcltn`,`ansungsetuju`,`pybsetuju`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sumber: SIMABSARA2017, tabel RiwayatCuti'
        SQL);

        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('entity', 50)->comment('Kunci entitas, nama tabel sumber mis. sysdb_pns, riwayatcuti');
            $table->string('label', 100)->comment('Label yang ditampilkan di halaman Sync Data');
            $table->string('status', 20)->default('pending')->comment('pending/running/success/failed');
            $table->unsignedInteger('records_synced')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->longText('message')->nullable();
            $table->timestamps();

            $table->index('entity');
        });

        Schema::create('kode_formasijabatan', function (Blueprint $table) {
            $table->id();
            $table->string('kd_tugas', 2)->nullable();
            $table->string('kd_jabatan', 7)->nullable();
            $table->string('formasijabatan', 255)->nullable();
            $table->integer('usia_pensiun')->nullable();
            $table->string('kodetugasinpassing', 50)->nullable();
            $table->string('isdisplay', 1)->nullable();
            $table->string('isplt', 1)->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique('kd_jabatan');
            $table->comment('Sumber: SIMABSARA2017, tabel KODE_FORMASIJABATAN');
        });

        Schema::create('sysdb_instansi', function (Blueprint $table) {
            $table->id();
            $table->string('ins_inscod', 2)->nullable();
            $table->string('ins_kndins', 1)->nullable();
            $table->string('ins_insnam', 250)->nullable();
            $table->string('ins_loccod', 4)->nullable();
            $table->string('ins_appcod', 2)->nullable();
            $table->string('ins_lasnip', 9)->nullable();
            $table->string('ins_ld2ano', 8)->nullable();
            $table->string('ins_lc2ano', 6)->nullable();
            $table->string('ins_higpos', 2)->nullable();
            $table->string('pengelola', 10)->nullable();
            $table->string('aktif', 1)->nullable();
            $table->string('aturan', 200)->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique('ins_inscod');
            $table->comment('Sumber: SIMABSARA2017, tabel SYSDB_INSTANSI (OPD Induk)');
        });

        Schema::create('sync_runs', function (Blueprint $table) {
            $table->id();
            $table->string('entity', 50)->unique();
            $table->timestamp('last_watermark')->nullable()
                ->comment('Nilai modidatetime maksimum dari sumber yang sudah diproses');
            $table->timestamp('last_synced_at')->nullable();
            $table->unsignedInteger('last_records_count')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_runs');
        Schema::dropIfExists('sysdb_instansi');
        Schema::dropIfExists('kode_formasijabatan');
        Schema::dropIfExists('sync_logs');
        Schema::dropIfExists('riwayatcuti');
        Schema::dropIfExists('sysdb_pns');
        Schema::dropIfExists('sysdb_pnskpg');
        Schema::dropIfExists('sysdb_pnsdetail');
        Schema::dropIfExists('tprofilunitkerja');
        Schema::dropIfExists('sysdb_wkgunitampungan');
        Schema::dropIfExists('sysdb_wkguni');
        Schema::dropIfExists('tkodecuti');
    }
};
