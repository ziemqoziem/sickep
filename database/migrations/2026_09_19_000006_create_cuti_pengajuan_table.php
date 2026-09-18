<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuti_pengajuan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengajuan', 50)->unique();
            $table->foreignId('pegawai_id')->constrained('tb_pegawai_aktif');
            $table->foreignId('atasan_langsung_pegawai_id')->nullable()
                ->constrained('tb_pegawai_aktif')->nullOnDelete();
            $table->foreignId('jenis_cuti_aturan_id')->constrained('jenis_cuti_aturan');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->unsignedInteger('lama_hari')->default(0);
            $table->text('alasan');
            $table->string('alamat_selama_cuti', 255)->nullable();
            $table->string('telepon_selama_cuti', 30)->nullable();
            $table->unsignedTinyInteger('keterangan_anak_ke')->nullable();
            $table->enum('status', ['diajukan', 'disetujui', 'ditolak', 'dibatalkan'])->default('diajukan');
            $table->unsignedTinyInteger('jumlah_tahap')->default(2);
            $table->string('nomor_sk', 50)->nullable();
            $table->date('tanggal_sk')->nullable();
            $table->uuid('qr_token')->unique();
            $table->string('pdf_path', 255)->nullable();
            $table->timestamp('pdf_generated_at')->nullable();
            $table->foreignId('dibuat_oleh')->constrained('users');
            $table->timestamps();

            $table->index(['pegawai_id', 'status']);
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuti_pengajuan');
    }
};
