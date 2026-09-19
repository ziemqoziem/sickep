<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Grant table untuk akses menu sidebar per role -- baris ADA berarti
     * role tsb boleh membuka menu itu (existence = granted, bukan kolom
     * boolean terpisah). Admin tidak pernah butuh baris di sini karena
     * selalu bisa akses semua menu (lihat layouts.sidebar &
     * EnsureMenuAccess). Menu yang ditandai 'admin' => true di
     * config/menu.php juga tidak pernah muncul di sini -- rute-nya sudah
     * dikunci middleware 'admin' terlepas dari isi tabel ini.
     */
    public function up(): void
    {
        Schema::create('role_menu_permissions', function (Blueprint $table) {
            $table->id();
            $table->enum('role', ['opd', 'user']);
            $table->string('menu_route', 150);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['role', 'menu_route']);
        });

        // Default: pertahankan perilaku yang sudah ada sebelum fitur ini
        // (semua menu non-admin terbuka untuk opd & user) -- supaya
        // pengguna aktif tidak mendadak kehilangan akses. Admin bisa
        // mempersempit lewat halaman Master > Hak Akses Menu kapan saja.
        $menuBisaDiatur = [
            'data.opd',
            'data.pegawai',
            'summary-cuti',
            'rekapitulasi',
            'cari-cuti',
            'cuti-baru.ajukan',
            'cuti-baru.riwayat',
            'cuti-baru.persetujuan',
        ];

        $now = now();
        $rows = [];
        foreach (['opd', 'user'] as $role) {
            foreach ($menuBisaDiatur as $route) {
                $rows[] = ['role' => $role, 'menu_route' => $route, 'created_at' => $now];
            }
        }

        DB::table('role_menu_permissions')->insert($rows);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_menu_permissions');
    }
};
