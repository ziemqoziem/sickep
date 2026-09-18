<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\EnvironmentWriter;
use App\Services\MdbConnection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PDOException;

class MdbConnectionController extends Controller
{
    public function edit(): View
    {
        return view('settings.koneksi', [
            'driver' => config('mdb.driver'),
            'host' => config('mdb.host'),
            'port' => config('mdb.port'),
            'database' => config('mdb.database'),
            'username' => config('mdb.username'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'driver' => ['required', 'string'],
            'host' => ['required', 'string'],
            'port' => ['required', 'string'],
            'database' => ['required', 'string'],
            'username' => ['required', 'string'],
            'password' => ['nullable', 'string'],
        ]);

        $password = $validated['password'] !== '' && $validated['password'] !== null
            ? $validated['password']
            : config('mdb.password');

        EnvironmentWriter::update([
            'MDB_DRIVER' => $validated['driver'],
            'MDB_HOST' => $validated['host'],
            'MDB_PORT' => $validated['port'],
            'MDB_DATABASE' => $validated['database'],
            'MDB_USERNAME' => $validated['username'],
            'MDB_PASSWORD' => $password,
        ]);

        config([
            'mdb.driver' => $validated['driver'],
            'mdb.host' => $validated['host'],
            'mdb.port' => $validated['port'],
            'mdb.database' => $validated['database'],
            'mdb.username' => $validated['username'],
            'mdb.password' => $password,
        ]);

        return redirect()
            ->route('settings.koneksi')
            ->with('status', 'Pengaturan koneksi berhasil disimpan.');
    }

    public function test(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'driver' => ['required', 'string'],
            'host' => ['required', 'string'],
            'port' => ['required', 'string'],
            'database' => ['required', 'string'],
            'username' => ['required', 'string'],
            'password' => ['nullable', 'string'],
        ]);

        if (! extension_loaded('pdo_odbc')) {
            return redirect()
                ->route('settings.koneksi')
                ->with('test_error', 'Ekstensi PHP "pdo_odbc" belum aktif di server ini. Aktifkan di php.ini (extension=pdo_odbc) lalu restart web server.');
        }

        $password = $validated['password'] !== '' && $validated['password'] !== null
            ? $validated['password']
            : config('mdb.password');

        $dsn = sprintf(
            'odbc:Driver={%s};Server=%s,%s;Database=%s;Uid=%s;Pwd=%s;',
            $validated['driver'],
            $validated['host'],
            $validated['port'],
            $validated['database'],
            MdbConnection::escapeOdbcValue($validated['username']),
            MdbConnection::escapeOdbcValue($password ?? '')
        );

        try {
            new \PDO($dsn);

            return redirect()
                ->route('settings.koneksi')
                ->with('test_success', 'Koneksi ke database SQL Server berhasil.');
        } catch (PDOException $e) {
            return redirect()
                ->route('settings.koneksi')
                ->with('test_error', 'Koneksi gagal: '.$e->getMessage());
        }
    }
}
