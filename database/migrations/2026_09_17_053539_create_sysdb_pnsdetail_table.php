<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sysdb_pnsdetail', function (Blueprint $table) {
            $table->id();
            $table->string('dtl_pnsnip', 9)->nullable();
            $table->string('dtl_pnstlp', 30)->nullable();
            $table->string('dtl_pnsfax', 15)->nullable();
            $table->string('dtl_pnsemail', 60)->nullable();
            $table->string('dtl_addrs', 60)->nullable();
            $table->string('dtl_poscod', 5)->nullable();
            $table->string('dtl_adrloc', 8)->nullable();
            $table->string('dtl_pnsnot', 255)->nullable();
            $table->dateTime('dtl_jargjtgl')->nullable();
            $table->string('dukuh_jalan', 50)->nullable()->comment('kolom sumber: dukuh/jalan');
            $table->string('desa', 50)->nullable();
            $table->string('kecamatan', 50)->nullable();
            $table->string('kabupaten', 50)->nullable();
            $table->string('provinsi', 50)->nullable();
            $table->string('status_data', 2)->nullable();
            $table->string('useruker', 100)->nullable();
            $table->dateTime('updtuker')->nullable();
            $table->string('dtl_pnsemailgoid', 50)->nullable();
            $table->string('dtl_addrs200', 200)->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique('dtl_pnsnip');
            $table->comment('Sumber: SIMABSARA2017, tabel SYSDB_PNSDETAIL');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sysdb_pnsdetail');
    }
};
