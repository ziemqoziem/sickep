<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kode_formasijabatan', function (Blueprint $table) {
            $table->id();
            $table->string('kd_tugas', 2)->nullable();
            $table->string('kd_jabatan', 7)->nullable();
            $table->string('formasijabatan', 255)->nullable();
            $table->integer('usia_pensiun')->nullable();
            $table->string('kodetugasinpassing', 50)->nullable();
            $table->string('isdisplay', 1)->nullable();
            $table->string('isplt', 1)->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique('kd_jabatan');
            $table->comment('Sumber: SIMABSARA2017, tabel KODE_FORMASIJABATAN');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kode_formasijabatan');
    }
};
