<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tprofilunitkerja', function (Blueprint $table) {
            $table->id();
            $table->string('kodeuker', 50)->nullable();
            $table->string('eselon', 50)->nullable();
            $table->string('kodeinstansigaji', 50)->nullable();
            $table->string('nipkepala', 50)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('indeks', 50)->nullable();
            $table->string('kodestatusdata', 50)->nullable();
            $table->string('isvisible', 1)->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique('kodeuker');
            $table->comment('Sumber: SIMABSARA2017, tabel TProfilUnitKerja');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tprofilunitkerja');
    }
};
