<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tkodecuti', function (Blueprint $table) {
            $table->id();
            $table->string('kodecuti', 2)->nullable();
            $table->string('keterangancuti', 100)->nullable();
            $table->string('kodesapa', 50)->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique('kodecuti');
            $table->comment('Sumber: SIMABSARA2017, tabel TKodeCuti');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tkodecuti');
    }
};
