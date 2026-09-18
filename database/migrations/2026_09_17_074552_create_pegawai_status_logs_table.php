<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
    }

    public function down(): void
    {
        Schema::dropIfExists('pegawai_status_logs');
    }
};
