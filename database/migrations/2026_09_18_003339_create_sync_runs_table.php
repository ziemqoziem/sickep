<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Watermark per entitas sinkron -- basis untuk incremental upsert
     * berikutnya (hanya proses baris sumber dengan modidatetime lebih baru
     * dari last_watermark), berbeda dari sync_logs yang mencatat histori
     * tiap kali proses sync dijalankan (audit trail, banyak baris).
     */
    public function up(): void
    {
        Schema::create('sync_runs', function (Blueprint $table) {
            $table->id();
            $table->string('entity', 50)->unique();
            $table->timestamp('last_watermark')->nullable()
                ->comment('Nilai modidatetime maksimum dari sumber yang sudah diproses');
            $table->timestamp('last_synced_at')->nullable();
            $table->unsignedInteger('last_records_count')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_runs');
    }
};
