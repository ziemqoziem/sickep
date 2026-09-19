<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "tb_opd_aktif" (unit kerja) dan "tb_pegawai_aktif" (data kepegawaian
     * aktif) sudah ada di database sejak sebelum proyek Laravel ini
     * dibuat, jadi TIDAK PERNAH punya migration sendiri -- migration lain
     * (users, master_pegawai_logs, cuti_pengajuan, dst) mengasumsikan
     * kedua tabel ini sudah ada lewat foreign key. Ini bikin instalasi
     * baru (database kosong) gagal migrate. Migration ini melengkapi
     * gap tsb, disalin dari struktur aktual di database yang berjalan,
     * dan SENGAJA dijalankan paling awal (timestamp 0000_00_00_000001,
     * lebih awal dari 0001_01_01_..._create_users_table) supaya foreign
     * key di migration lain valid.
     *
     * Catatan: kolom "tb_opd_aktif.kepala_pegawai_id" tadinya (di
     * database lama) punya foreign key ke tabel "pegawai" yang sudah
     * lama dihapus -- FK itu rusak/tidak pernah valid (selalu NULL,
     * lihat requirement.md bagian 5.4). Di sini kolomnya tetap ada
     * (dipertahankan untuk kompatibilitas), tapi TANPA foreign key yang
     * mengarah ke tabel yang tidak ada, supaya migration bisa jalan di
     * instalasi baru. Deteksi "siapa Kepala OPD" di aplikasi sudah lama
     * memakai pencocokan teks nip = nip_kepala, bukan kolom ini.
     */
    public function up(): void
    {
        Schema::create('tb_opd_aktif', function (Blueprint $table) {
            $table->id();
            $table->string('idunor', 30)->unique()->comment('Kode resmi OPD/sub-unit, mis. 5.03.5.04.0.00.01.0000');
            $table->string('uraiunor', 255)->comment('Nama OPD atau sub-unit');
            $table->string('akronim', 50)->nullable()->comment('NULL berarti baris ini sub-unit, bukan OPD tingkat atas');
            $table->string('nama_jabatan', 200)->nullable();
            $table->string('nama_kepala', 150)->nullable();
            $table->string('nip_kepala', 18)->nullable();
            $table->string('pangkat_kepala', 50)->nullable();
            $table->string('golongan_kepala', 10)->nullable();
            $table->enum('status_jabatan', ['Definitif', 'Pelaksana Tugas (Plt)', 'Pelaksana Harian (Plh)'])->nullable();
            $table->unsignedBigInteger('kepala_pegawai_id')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('tb_pegawai_aktif', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 18)->unique();
            $table->string('nama', 150);
            $table->string('gelar_depan', 30)->nullable();
            $table->string('gelar_belakang', 100)->nullable();
            $table->enum('status_kepegawaian', ['PNS', 'PPPK', 'PPPK Paruh Waktu', 'Calon PNS']);
            $table->string('golongan', 10)->nullable();
            $table->string('pangkat', 100)->nullable();
            $table->string('pendidikan', 150)->nullable();
            $table->string('jabatan', 150)->nullable();
            $table->string('jenis_jabatan', 50)->nullable();
            $table->string('unit_kerja', 255)->nullable();
            $table->foreignId('opd_id')->constrained('tb_opd_aktif');
            $table->enum('status_aktif', ['Aktif', 'Tidak Aktif'])->default('Aktif');
            $table->date('tanggal_nonaktif')->nullable();
            $table->string('keterangan_nonaktif', 255)->nullable();
            $table->integer('sisa_cuti_tahunan')->default(12);
            $table->timestamps();

            $table->index('nama');
            $table->index('status_aktif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_pegawai_aktif');
        Schema::dropIfExists('tb_opd_aktif');
    }
};
