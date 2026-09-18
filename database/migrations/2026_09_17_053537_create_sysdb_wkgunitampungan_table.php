<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sysdb_wkgunitampungan', function (Blueprint $table) {
            $table->id();
            $table->string('wku_inscod', 2)->nullable();
            $table->string('wku_wrkcod', 64)->nullable();
            $table->string('wku_name', 40)->nullable();
            $table->string('status', 1)->nullable();
            $table->string('wku_ukerpjg', 244)->nullable();
            $table->string('wku_lama', 64)->nullable();
            $table->longText('wku_alamat')->nullable();
            $table->string('wku_telepon', 20)->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique('wku_wrkcod');
            $table->comment('Sumber: SIMABSARA2017, tabel SYSDB_WKGUNITAMPUNGAN');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sysdb_wkgunitampungan');
    }
};
