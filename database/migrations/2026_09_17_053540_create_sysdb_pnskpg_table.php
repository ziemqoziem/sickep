<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sysdb_pnskpg', function (Blueprint $table) {
            $table->id();
            $table->string('kpg_pnsnip', 9)->nullable();
            $table->string('kpg_payroll', 15)->nullable();
            $table->string('kpg_rekkor', 15)->nullable();
            $table->string('kpg_karkre', 16)->nullable();
            $table->string('kpg_asrnsi', 15)->nullable();
            $table->decimal('kpg_jnasrn', 1, 0)->nullable();
            $table->decimal('kpg_kdebit', 1, 0)->nullable();
            $table->string('kpg_tasnum', 15)->nullable();
            $table->string('kpg_asknum', 15)->nullable();
            $table->string('kpg_farmsts', 1)->nullable();
            $table->string('kpg_npwnum', 15)->nullable();
            $table->string('kpg_nik', 16)->nullable();
            $table->string('kpg_kk', 16)->nullable();
            $table->string('iduser', 50)->nullable();
            $table->string('modidatetime', 50)->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique('kpg_pnsnip');
            $table->comment('Sumber: SIMABSARA2017, tabel SYSDB_PNSKPG');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sysdb_pnskpg');
    }
};
