<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Seluruh tabel modul "Cuti Baru" (pengajuan & persetujuan cuti
     * berjenjang) -- digabung dari 7 migration terpisah yang dibuat
     * berurutan. Urutan Schema::create di bawah ini SENGAJA dipertahankan
     * sama seperti urutan migration aslinya karena mengikuti rantai FK:
     * jenis_cuti_aturan & opd_admins tidak bergantung pada tabel lain di
     * grup ini -> cuti_saldo_tahunan & cuti_pengajuan bergantung ke
     * jenis_cuti_aturan -> cuti_pengajuan_tahap/lampiran/log bergantung
     * ke cuti_pengajuan.
     */
    public function up(): void
    {
        Schema::create('opd_admins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('opd_id')->constrained('tb_opd_aktif')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'opd_id']);
        });

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

        Schema::create('cuti_pengajuan_lampiran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuti_pengajuan_id')->constrained('cuti_pengajuan')->cascadeOnDelete();
            $table->string('nama_dokumen', 150);
            $table->string('path_file', 255);
            $table->foreignId('diunggah_oleh')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('cuti_pengajuan_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuti_pengajuan_id')->constrained('cuti_pengajuan')->cascadeOnDelete();
            $table->string('status_sebelum', 20)->nullable();
            $table->string('status_sesudah', 20);
            $table->foreignId('oleh')->constrained('users');
            $table->text('catatan')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuti_pengajuan_log');
        Schema::dropIfExists('cuti_pengajuan_lampiran');
        Schema::dropIfExists('cuti_pengajuan_tahap');
        Schema::dropIfExists('cuti_pengajuan');
        Schema::dropIfExists('cuti_saldo_tahunan');
        Schema::dropIfExists('jenis_cuti_aturan');
        Schema::dropIfExists('opd_admins');
    }
};
