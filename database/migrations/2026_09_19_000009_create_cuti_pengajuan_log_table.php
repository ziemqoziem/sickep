<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

    public function down(): void
    {
        Schema::dropIfExists('cuti_pengajuan_log');
    }
};
