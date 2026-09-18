<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_pegawai_mutasi_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('tb_pegawai_aktif')->cascadeOnDelete();
            $table->string('nip', 18);
            $table->string('nama', 150)->nullable();

            $table->foreignId('opd_asal_id')->nullable()->constrained('tb_opd_aktif')->nullOnDelete();
            $table->string('opd_asal_nama', 255)->nullable();
            $table->foreignId('opd_tujuan_id')->constrained('tb_opd_aktif');
            $table->string('opd_tujuan_nama', 255);

            $table->string('unit_kerja_asal', 255)->nullable();
            $table->string('unit_kerja_tujuan', 255)->nullable();

            $table->string('nomor_sk', 100)->nullable();
            $table->date('tanggal_sk')->nullable();
            $table->date('tanggal_efektif');
            $table->text('keterangan')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('nip');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_pegawai_mutasi_logs');
    }
};
