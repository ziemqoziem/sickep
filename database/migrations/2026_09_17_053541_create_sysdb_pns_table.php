<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sysdb_pns', function (Blueprint $table) {
            $table->id();

            $table->string('pns_pnsnip', 9)->nullable();
            $table->string('pns_pnsnam', 40)->nullable();
            $table->enum('status_aktif', ['Aktif', 'Tidak Aktif'])->default('Tidak Aktif')
                ->comment('Ditentukan dengan mencocokkan nip_baru terhadap tb_pegawai_dump (daftar pegawai aktif)');
            $table->boolean('manual_override_status')->default(false)
                ->comment('true = status_aktif diatur manual (nonaktifkan/aktifkan), dilewati saat sinkronisasi ulang');
            $table->string('pns_ftitle', 15)->nullable();
            $table->string('pns_pnspob', 4)->nullable();
            $table->string('pns_pnsdob', 8)->nullable();
            $table->string('pns_sexcod', 1)->nullable();
            $table->string('pns_rlgcod', 1)->nullable();
            $table->string('pns_scicod', 7)->nullable();
            $table->string('pns_strnco', 2)->nullable();
            $table->string('pns_asefdt', 8)->nullable();
            $table->dateTime('pns_ccsefd')->nullable();
            $table->string('pns_kndpns', 2)->nullable();
            $table->string('pns_srcdta', 1)->nullable();
            $table->string('pns_basrec', 1)->nullable();
            $table->string('pns_csefdt', 8)->nullable();
            $table->string('pns_stapns', 1)->nullable();
            $table->string('pns_oatsta', 1)->nullable();
            $table->string('pns_kpgsrn', 11)->nullable();
            $table->string('pns_mincod', 2)->nullable();
            $table->string('pns_inscod', 2)->nullable();
            $table->string('override_opd_kode', 2)->nullable()
                ->comment('Hasil mutasi manual, menimpa pns_inscod saat ditampilkan');
            $table->string('pns_wkucod', 10)->nullable();
            $table->string('override_unit_kerja_kode', 64)->nullable()
                ->comment('Hasil mutasi manual, menimpa pns_wkucod saat ditampilkan');
            $table->string('override_unit_kerja_nama', 255)->nullable()
                ->comment('Nama unit kerja tujuan mutasi, dipakai jika tidak match ke sysdb_wkguni');
            $table->string('pns_genpos', 2)->nullable();
            $table->string('pns_echcod', 2)->nullable();
            $table->dateTime('pns_inadte')->nullable();
            $table->dateTime('pns_echefd')->nullable();
            $table->string('pns_strtng', 1)->nullable();
            $table->string('pns_ftrcod', 3)->nullable();
            $table->string('pns_cfpcod', 5)->nullable();
            $table->decimal('pns_maincp', 6, 3)->nullable();
            $table->decimal('pns_suppcp', 6, 3)->nullable();
            $table->string('pns_stlud', 1)->nullable();
            $table->string('pns_kndsal', 1)->nullable();
            $table->string('pns_crncod', 2)->nullable();
            $table->dateTime('pns_crndte')->nullable();
            $table->string('pns_crnsta', 1)->nullable();
            $table->decimal('pns_wprank', 4, 2)->nullable();
            $table->string('pns_lrncod', 1)->nullable();
            $table->string('pns_pofcod', 5)->nullable();
            $table->string('pns_ktuaco', 2)->nullable();
            $table->string('pns_wklcod', 8)->nullable();
            $table->string('pns_marsta', 1)->nullable();
            $table->decimal('pns_dpcwfe', 1, 0)->nullable();
            $table->decimal('pns_dpcchl', 2, 0)->nullable();
            $table->string('pns_awrsta', 1)->nullable();
            $table->string('pns_pnlcod', 1)->nullable();
            $table->string('pns_stepns', 2)->nullable();
            $table->string('pns_pospso', 2)->nullable();
            $table->string('pns_proorg', 1)->nullable();
            $table->string('pns_solorg', 1)->nullable();
            $table->string('pns_golkar', 1)->nullable();
            $table->string('pns_mngloc', 1)->nullable();
            $table->string('pns_reasta', 1)->nullable();
            $table->dateTime('pns_penefd')->nullable();
            $table->string('pns_repnam', 40)->nullable();
            $table->string('pns_creaby', 8)->nullable();
            $table->dateTime('pns_creadt')->nullable();
            $table->string('pns_updtby', 8)->nullable();
            $table->dateTime('pns_updtdt')->nullable();
            $table->string('pns_btchid', 12)->nullable();
            $table->dateTime('pns_postdt')->nullable();
            $table->string('pns_tercod', 2)->nullable();
            $table->string('pns_tasnum', 15)->nullable();
            $table->string('pns_asknum', 15)->nullable();
            $table->string('pns_korcod', 1)->nullable();
            $table->string('pns_famsts', 1)->nullable();
            $table->string('pns_npwnum', 15)->nullable();
            $table->string('pns_niknum', 17)->nullable();
            $table->string('pns_pnsadr', 50)->nullable();
            $table->string('pns_poscod', 5)->nullable();
            $table->string('pns_adrloc', 8)->nullable();
            $table->string('pns_lahirloc', 60)->nullable();
            $table->string('pns_unkerloc', 60)->nullable();
            $table->dateTime('pns_tgllstr')->nullable();
            $table->string('pns_jmjmstr', 4)->nullable();
            $table->dateTime('pns_tgllfun')->nullable();
            $table->string('pns_jmjmfun', 4)->nullable();
            $table->string('pns_thllpdk', 4)->nullable();
            $table->string('pns_jargjcd', 3)->nullable();
            $table->string('pns_tmtpen', 9)->nullable();
            $table->dateTime('pns_proses')->nullable();
            $table->dateTime('pns_tmtgaji')->nullable();
            $table->string('pns_ketgaji', 200)->nullable();
            $table->string('pns_jabtam', 2)->nullable();
            $table->dateTime('pns_tmttam')->nullable();
            $table->string('pns_kodgr2', 2)->nullable();
            $table->decimal('pns_pmkth', 2, 0)->nullable();
            $table->decimal('pns_pmkbl', 2, 0)->nullable();
            $table->string('kode_formasi', 7)->nullable();
            $table->string('nomor_sk_tugas', 30)->nullable();
            $table->dateTime('tgl_sk_tugas')->nullable();
            $table->string('kd_pjb', 2)->nullable();
            $table->string('nip_baru', 18)->nullable();
            $table->string('unit_lama', 10)->nullable();
            $table->decimal('pns_thnlls', 4, 0)->nullable();
            $table->string('pns_kkrsno1', 15)->nullable();
            $table->string('pns_nikno', 16)->nullable();
            $table->string('pns_jabattam', 10)->nullable();
            $table->string('pns_rtitle', 25)->nullable();
            $table->string('pns_kksrno', 20)->nullable();
            $table->string('status_data', 2)->nullable();
            $table->string('useruker', 100)->nullable();
            $table->dateTime('updtuker')->nullable();
            $table->string('idpnsbkn', 50)->nullable();
            $table->string('eselonbkn', 2)->nullable();
            $table->string('pns_kodetpp', 10)->nullable();
            $table->string('pns_stepnstpp', 5)->nullable();
            $table->string('pns_crncodtpp', 3)->nullable();
            $table->string('pns_jabatantpp', 10)->nullable();
            $table->string('pns_unitkerjatpp', 10)->nullable();
            $table->string('pns_istpp', 1)->nullable();
            $table->string('pns_issae', 1)->nullable();
            $table->string('pns_isinspektorat', 1)->nullable();
            $table->string('pns_ishukdis', 1)->nullable();
            $table->string('pns_iskinerja', 1)->nullable();

            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique('pns_pnsnip');
            $table->index('nip_baru');
            $table->comment('Sumber: SIMABSARA2017, tabel SYSDB_PNS');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sysdb_pns');
    }
};
