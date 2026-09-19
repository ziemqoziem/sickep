<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jejak aktivitas pengguna aplikasi (siapa, dari IP mana, aktivitas
     * apa, kapan) untuk kebutuhan monitoring & audit -- diisi otomatis
     * oleh middleware LogActivity di setiap request, bukan diisi manual.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_pengguna', 255)->nullable()->comment('Snapshot nama, tetap ada walau akun dihapus/tamu');
            $table->string('ip_address', 45)->nullable();
            $table->string('method', 10)->nullable();
            $table->string('url', 500)->nullable();
            $table->string('route_name', 150)->nullable();
            $table->string('aktivitas', 255);
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id');
            $table->index('created_at');
            $table->index('route_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
