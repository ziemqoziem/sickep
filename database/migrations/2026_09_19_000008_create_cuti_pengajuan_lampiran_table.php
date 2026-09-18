<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuti_pengajuan_lampiran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuti_pengajuan_id')->constrained('cuti_pengajuan')->cascadeOnDelete();
            $table->string('nama_dokumen', 150);
            $table->string('path_file', 255);
            $table->foreignId('diunggah_oleh')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuti_pengajuan_lampiran');
    }
};
