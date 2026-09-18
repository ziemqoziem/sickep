<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuti_pengajuan_tahap', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuti_pengajuan_id')->constrained('cuti_pengajuan')->cascadeOnDelete();
            $table->unsignedTinyInteger('urutan');
            $table->enum('jenjang', ['atasan_langsung', 'kepala_unit_kerja', 'admin']);
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak', 'dilewati'])->default('menunggu');
            $table->foreignId('penyetuju_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diputuskan_pada')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['cuti_pengajuan_id', 'urutan']);
            $table->index(['jenjang', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuti_pengajuan_tahap');
    }
};
