<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sysdb_instansi', function (Blueprint $table) {
            $table->id();
            $table->string('ins_inscod', 2)->nullable();
            $table->string('ins_kndins', 1)->nullable();
            $table->string('ins_insnam', 250)->nullable();
            $table->string('ins_loccod', 4)->nullable();
            $table->string('ins_appcod', 2)->nullable();
            $table->string('ins_lasnip', 9)->nullable();
            $table->string('ins_ld2ano', 8)->nullable();
            $table->string('ins_lc2ano', 6)->nullable();
            $table->string('ins_higpos', 2)->nullable();
            $table->string('pengelola', 10)->nullable();
            $table->string('aktif', 1)->nullable();
            $table->string('aturan', 200)->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique('ins_inscod');
            $table->comment('Sumber: SIMABSARA2017, tabel SYSDB_INSTANSI (OPD Induk)');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sysdb_instansi');
    }
};
