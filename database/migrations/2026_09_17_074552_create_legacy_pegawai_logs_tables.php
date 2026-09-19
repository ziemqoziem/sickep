<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jejak audit aksi nonaktifkan/aktifkan/mutasi pada data pegawai
     * legacy (sysdb_pns, menu "Pegawai Simabs") -- digabung dari 2
     * migration terpisah.
     */
    public function up(): void
    {
        Schema::create('pegawai_status_logs', function (Blueprint $table) {
            $table->id();
            $table->string('pns_pnsnip', 9);
            $table->string('nip_baru', 18)->nullable();
            $table->string('nama', 255)->nullable();
            $table->enum('status_baru', ['Aktif', 'Tidak Aktif']);
            $table->text('alasan')->nullable();
            $table->date('tanggal_efektif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('pns_pnsnip');
        });

        Schema::create('pegawai_mutasi_logs', function (Blueprint $table) {
            $table->id();
            $table->string('pns_pnsnip', 9);
            $table->string('nip_baru', 18)->nullable();
            $table->string('nama', 255)->nullable();

            $table->string('opd_asal_kode', 2)->nullable();
            $table->string('opd_asal_nama', 255)->nullable();
            $table->string('opd_tujuan_kode', 2);
            $table->string('opd_tujuan_nama', 255);

            $table->string('unit_kerja_asal_kode', 64)->nullable();
            $table->string('unit_kerja_asal_nama', 255)->nullable();
            $table->string('unit_kerja_tujuan_kode', 64)->nullable();
            $table->string('unit_kerja_tujuan_nama', 255)->nullable();

            $table->string('nomor_sk', 100)->nullable();
            $table->date('tanggal_sk')->nullable();
            $table->date('tanggal_efektif');
            $table->text('keterangan')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('pns_pnsnip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawai_mutasi_logs');
        Schema::dropIfExists('pegawai_status_logs');
    }
};
