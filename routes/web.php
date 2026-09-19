<?php

use App\Http\Controllers\Cuti\CariCutiController;
use App\Http\Controllers\Cuti\CutiPengajuanController;
use App\Http\Controllers\Cuti\PersetujuanAkhirCutiController;
use App\Http\Controllers\Cuti\RekapitulasiController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\RoleDashboardController;
use App\Http\Controllers\Data\OpdController;
use App\Http\Controllers\Data\PegawaiActionController;
use App\Http\Controllers\Data\PegawaiController;
use App\Http\Controllers\Master\ActivityLogController;
use App\Http\Controllers\Master\JenisCutiAturanController;
use App\Http\Controllers\Master\MasterPegawaiActionController;
use App\Http\Controllers\Master\MasterPegawaiController;
use App\Http\Controllers\Master\MenuAksesController;
use App\Http\Controllers\Master\PengumumanController;
use App\Http\Controllers\Master\PenggunaController;
use App\Http\Controllers\Master\UnitKerjaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Settings\MdbConnectionController;
use App\Http\Controllers\Sync\SyncDataController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Dashboard beranda -- tampilan berbeda per role (user/opd/admin), lihat
// RoleDashboardController. KPI historis lengkap dari data sync legacy
// tetap ada terpisah di /summary-cuti (menu "Summary Cuti").
Route::get('/dashboard', [RoleDashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/summary-cuti', [DashboardController::class, 'index'])->name('summary-cuti');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');

    Route::get('/data/opd', [OpdController::class, 'index'])->name('data.opd');
    Route::get('/data/pegawai', [PegawaiController::class, 'index'])->name('data.pegawai');
    Route::get('/cari-cuti', [CariCutiController::class, 'index'])->name('cari-cuti');
    Route::get('/rekapitulasi', [RekapitulasiController::class, 'index'])->name('rekapitulasi');
    Route::get('/rekapitulasi/detail', [RekapitulasiController::class, 'detail'])->name('rekapitulasi.detail');

    Route::prefix('cuti-baru')->name('cuti-baru.')->group(function () {
        Route::get('/ajukan', [CutiPengajuanController::class, 'create'])->name('ajukan');
        Route::post('/ajukan', [CutiPengajuanController::class, 'store'])->name('ajukan.store');
        Route::get('/riwayat', [CutiPengajuanController::class, 'myIndex'])->name('riwayat');
        Route::get('/persetujuan', [CutiPengajuanController::class, 'persetujuan'])->name('persetujuan');
        Route::post('/persetujuan/{cutiPengajuanTahap}/setujui', [CutiPengajuanController::class, 'setujui'])->name('persetujuan.setujui');
        Route::post('/persetujuan/{cutiPengajuanTahap}/tolak', [CutiPengajuanController::class, 'tolak'])->name('persetujuan.tolak');

        // Rute statis "persetujuan-akhir/..." WAJIB didaftarkan sebelum
        // wildcard GET /{cutiPengajuan} di bawah -- kalau tidak, Laravel
        // mencocokkan wildcard itu duluan (mengira "persetujuan-akhir"
        // adalah sebuah ID) dan selalu menghasilkan 404.
        Route::middleware('admin')->group(function () {
            Route::get('/persetujuan-akhir', [PersetujuanAkhirCutiController::class, 'index'])->name('persetujuan-akhir');
            Route::post('/persetujuan-akhir/{cutiPengajuanTahap}/setujui', [PersetujuanAkhirCutiController::class, 'setujui'])->name('persetujuan-akhir.setujui');
            Route::post('/persetujuan-akhir/{cutiPengajuanTahap}/tolak', [PersetujuanAkhirCutiController::class, 'tolak'])->name('persetujuan-akhir.tolak');
        });

        Route::post('/{cutiPengajuan}/batalkan', [CutiPengajuanController::class, 'cancel'])->name('batalkan');
        Route::get('/{cutiPengajuan}', [CutiPengajuanController::class, 'show'])->name('show');
    });
});

Route::middleware(['auth', 'admin'])->prefix('settings')->name('settings.')->group(function () {
    Route::get('/koneksi', [MdbConnectionController::class, 'edit'])->name('koneksi');
    Route::put('/koneksi', [MdbConnectionController::class, 'update'])->name('koneksi.update');
    Route::post('/koneksi/test', [MdbConnectionController::class, 'test'])->name('koneksi.test');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/sync-data', [SyncDataController::class, 'index'])->name('sync-data');
    Route::post('/sync-data/{entity}', [SyncDataController::class, 'run'])->name('sync-data.run');
});

Route::middleware(['auth', 'admin'])->prefix('pegawai')->name('pegawai.')->group(function () {
    Route::post('/{nip}/nonaktifkan', [PegawaiActionController::class, 'nonaktifkan'])->name('nonaktifkan');
    Route::post('/{nip}/aktifkan', [PegawaiActionController::class, 'aktifkan'])->name('aktifkan');
    Route::post('/{nip}/mutasi', [PegawaiActionController::class, 'mutasi'])->name('mutasi');
});

Route::middleware(['auth', 'admin'])->prefix('master')->name('master.')->group(function () {
    Route::get('/unit-kerja', [UnitKerjaController::class, 'index'])->name('unit-kerja');
    Route::post('/unit-kerja', [UnitKerjaController::class, 'store'])->name('unit-kerja.store');
    Route::put('/unit-kerja/{unitKerja}', [UnitKerjaController::class, 'update'])->name('unit-kerja.update');
    Route::delete('/unit-kerja/{unitKerja}', [UnitKerjaController::class, 'destroy'])->name('unit-kerja.destroy');

    Route::get('/pengguna', [PenggunaController::class, 'index'])->name('pengguna');
    Route::post('/pengguna', [PenggunaController::class, 'store'])->name('pengguna.store');
    Route::post('/pengguna/generate', [PenggunaController::class, 'generate'])->name('pengguna.generate');
    Route::put('/pengguna/{pengguna}', [PenggunaController::class, 'update'])->name('pengguna.update');
    Route::post('/pengguna/{pengguna}/nonaktifkan', [PenggunaController::class, 'nonaktifkan'])->name('pengguna.nonaktifkan');
    Route::post('/pengguna/{pengguna}/aktifkan', [PenggunaController::class, 'aktifkan'])->name('pengguna.aktifkan');
    Route::delete('/pengguna/{pengguna}', [PenggunaController::class, 'destroy'])->name('pengguna.destroy');

    Route::get('/pegawai', [MasterPegawaiController::class, 'index'])->name('pegawai');
    Route::get('/pegawai-search', [MasterPegawaiController::class, 'search'])->name('pegawai.search');
    Route::post('/pegawai', [MasterPegawaiController::class, 'store'])->name('pegawai.store');
    Route::put('/pegawai/{pegawai}', [MasterPegawaiController::class, 'update'])->name('pegawai.update');
    Route::post('/pegawai/{pegawai}/nonaktifkan', [MasterPegawaiActionController::class, 'nonaktifkan'])->name('pegawai.nonaktifkan');
    Route::post('/pegawai/{pegawai}/aktifkan', [MasterPegawaiActionController::class, 'aktifkan'])->name('pegawai.aktifkan');
    Route::post('/pegawai/{pegawai}/mutasi', [MasterPegawaiActionController::class, 'mutasi'])->name('pegawai.mutasi');

    Route::get('/jenis-cuti', [JenisCutiAturanController::class, 'index'])->name('jenis-cuti');
    Route::post('/jenis-cuti', [JenisCutiAturanController::class, 'store'])->name('jenis-cuti.store');
    Route::put('/jenis-cuti/{jenisCuti}', [JenisCutiAturanController::class, 'update'])->name('jenis-cuti.update');

    Route::get('/log-aktivitas', [ActivityLogController::class, 'index'])->name('log-aktivitas');

    Route::get('/pengumuman', [PengumumanController::class, 'index'])->name('pengumuman');
    Route::post('/pengumuman', [PengumumanController::class, 'store'])->name('pengumuman.store');
    Route::put('/pengumuman/{pengumuman}', [PengumumanController::class, 'update'])->name('pengumuman.update');
    Route::delete('/pengumuman/{pengumuman}', [PengumumanController::class, 'destroy'])->name('pengumuman.destroy');

    Route::get('/akses-menu', [MenuAksesController::class, 'index'])->name('akses-menu');
    Route::post('/akses-menu', [MenuAksesController::class, 'update'])->name('akses-menu.update');
});

require __DIR__.'/auth.php';
