<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_pegawai_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('tb_pegawai_aktif')->cascadeOnDelete();
            $table->string('nip', 18);
            $table->string('nama', 150)->nullable();
            $table->enum('status_baru', ['Aktif', 'Tidak Aktif']);
            $table->text('alasan')->nullable();
            $table->date('tanggal_efektif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('nip');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_pegawai_status_logs');
    }
};
