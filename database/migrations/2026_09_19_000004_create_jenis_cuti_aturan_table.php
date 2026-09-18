<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_cuti_aturan', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('kodecuti_sumber', 10)->nullable();
            $table->string('nama', 100);
            $table->unsignedInteger('syarat_masa_kerja_bulan')->nullable();
            $table->unsignedInteger('jatah_hari')->nullable();
            $table->unsignedInteger('carry_over_hari')->nullable();
            $table->unsignedInteger('maks_hari')->nullable();
            $table->boolean('perlu_dokumen')->default(false);
            $table->boolean('butuh_persetujuan_admin')->default(false);
            $table->text('keterangan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_cuti_aturan');
    }
};
