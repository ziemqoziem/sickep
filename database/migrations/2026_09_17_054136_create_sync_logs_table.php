<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('entity', 50)->comment('Kunci entitas, nama tabel sumber mis. sysdb_pns, riwayatcuti');
            $table->string('label', 100)->comment('Label yang ditampilkan di halaman Sync Data');
            $table->string('status', 20)->default('pending')->comment('pending/running/success/failed');
            $table->unsignedInteger('records_synced')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->longText('message')->nullable();
            $table->timestamps();

            $table->index('entity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};
