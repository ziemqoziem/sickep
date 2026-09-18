<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayatcuti', function (Blueprint $table) {
            $table->id();

            $table->string('nip', 18)->nullable();
            $table->string('idcltn', 255)->nullable();
            $table->string('jeniscuti', 2)->nullable();
            $table->string('nomorskcltn', 255)->nullable();
            $table->dateTime('tanggalskcltn')->nullable();
            $table->date('tanggalawalcltn')->nullable();
            $table->date('tanggalakhircltn')->nullable();
            $table->dateTime('tanggalaktifcltn')->nullable();
            $table->string('nobkn', 255)->nullable();
            $table->dateTime('tanggalbkn')->nullable();
            $table->string('iduser', 50)->nullable();
            $table->string('isvisible', 1)->nullable();
            $table->string('modidatetime', 50)->nullable();
            $table->integer('lamahari')->nullable();
            $table->string('kodesatuancuti', 2)->nullable();
            $table->string('gelardepan', 10)->nullable();
            $table->string('gelarbelakang', 10)->nullable();
            $table->string('jabatan', 10)->nullable();
            $table->string('unitkerja', 10)->nullable();
            $table->string('masakerja', 5)->nullable();
            $table->string('alasancuti', 200)->nullable();
            $table->string('catatancuti', 200)->nullable();
            $table->integer('n2')->nullable();
            $table->string('n2ket', 200)->nullable();
            $table->integer('n1')->nullable();
            $table->string('n1ket', 200)->nullable();
            $table->integer('n')->nullable();
            $table->string('nket', 200)->nullable();
            $table->integer('besar')->nullable();
            $table->integer('sakit')->nullable();
            $table->integer('melahirkan')->nullable();
            $table->integer('penting')->nullable();
            $table->integer('cltn')->nullable();
            $table->string('alamatcuti', 200)->nullable();
            $table->string('telpon', 20)->nullable();
            $table->integer('ansungsetuju')->nullable();
            $table->integer('ansungrubah')->nullable();
            $table->string('ansungrubahalasan', 180)->nullable();
            $table->integer('ansungtangguh')->nullable();
            $table->string('ansungtangguhalasan', 10)->nullable();
            $table->integer('ansungtidak')->nullable();
            $table->string('ansungtidakalasan', 180)->nullable();
            $table->string('nipansung', 18)->nullable();
            $table->integer('pybsetuju')->nullable();
            $table->integer('pybrubah')->nullable();
            $table->string('pybrubahalasan', 180)->nullable();
            $table->integer('pybtangguh')->nullable();
            $table->string('pybtangguhalasan', 180)->nullable();
            $table->integer('pybtidak')->nullable();
            $table->string('pybtidakalasan', 180)->nullable();
            $table->string('nippyb', 18)->nullable();
            $table->dateTime('tglproses')->nullable();
            $table->string('cutialasanpenting', 2)->nullable();
            $table->integer('umpegtahunan')->nullable();
            $table->integer('umpegbesar')->nullable();
            $table->integer('umpegsakit')->nullable();
            $table->integer('umpegmelahirkan')->nullable();
            $table->integer('umpegpenting')->nullable();
            $table->integer('umpegcltn')->nullable();
            $table->string('catatanumpeg', 2)->nullable();
            $table->string('dalamluarnegeri', 2)->nullable();
            $table->string('umpegnip', 18)->nullable();
            $table->string('catatanangsung', 2)->nullable();
            $table->string('ansungjabatanlain', 200)->nullable();
            $table->string('pybatasanlain', 200)->nullable();
            $table->string('ansungjabatan', 10)->nullable();
            $table->string('ansungunitkerja', 10)->nullable();
            $table->string('ansungtambahan', 150)->nullable();
            $table->string('pybjabatan', 10)->nullable();
            $table->string('pybunitkerja', 10)->nullable();
            $table->string('pybtambahan', 150)->nullable();
            $table->string('nomorpengantar', 50)->nullable();
            $table->dateTime('tanggalpengantar')->nullable();
            $table->string('golru', 10)->nullable();
            $table->string('idbatchluarnegeri', 50)->nullable();

            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['nip', 'jeniscuti', 'tanggalawalcltn'], 'riwayatcuti_natural_key');

            // Index gabungan untuk Dashboard & Cari Cuti, yang selalu
            // memfilter tanggal + status persetujuan bersamaan (index
            // tanggal sendiri tidak cukup, MySQL jatuh ke full table scan
            // karena masih perlu row lookup terpisah untuk ansungsetuju/
            // pybsetuju). Prefiks tanggalawalcltn-nya juga otomatis
            // memenuhi query yang hanya filter tanggal saja.
            $table->index(['tanggalawalcltn', 'ansungsetuju', 'pybsetuju'], 'riwayatcuti_tanggal_status_index');

            $table->comment('Sumber: SIMABSARA2017, tabel RiwayatCuti');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayatcuti');
    }
};
