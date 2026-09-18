<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuti_saldo_tahunan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('tb_pegawai_aktif')->cascadeOnDelete();
            $table->unsignedSmallInteger('tahun');
            $table->unsignedInteger('jatah')->default(12);
            $table->unsignedInteger('carry_over_masuk')->default(0);
            $table->unsignedInteger('tambahan_cuti_bersama')->default(0);
            $table->unsignedInteger('terpakai')->default(0);
            $table->integer('sisa')->default(0);
            $table->timestamps();

            $table->unique(['pegawai_id', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuti_saldo_tahunan');
    }
};
