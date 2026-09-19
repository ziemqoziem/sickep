<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    /**
     * Rute yang sengaja tidak dicatat -- terlalu sering dipanggil
     * (polling/verifikasi email yang sebenarnya tidak dipakai di
     * aplikasi ini) sehingga hanya jadi noise di log.
     */
    protected const RUTE_DIKECUALIKAN = [
        'verification.notice',
        'verification.verify',
        'verification.send',
    ];

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Dicatat lewat terminate() (setelah respons dikirim ke browser)
     * supaya proses logging tidak menambah waktu tunggu pengguna.
     */
    public function terminate(Request $request, Response $response): void
    {
        $route = $request->route();

        if (! $route) {
            return;
        }

        $routeName = $route->getName();

        if ($routeName && in_array($routeName, self::RUTE_DIKECUALIKAN, true)) {
            return;
        }

        $user = $request->user();

        ActivityLog::create([
            'user_id' => $user?->id,
            'nama_pengguna' => $user?->name ?? 'Tamu',
            'ip_address' => $request->ip(),
            'method' => $request->method(),
            'url' => Str::limit($request->fullUrl(), 490, ''),
            // Beberapa rute bawaan Breeze (login POST, register POST, dll)
            // sengaja tidak diberi nama oleh scaffolding-nya -- pakai path
            // sebagai fallback supaya tetap tercatat, bukan malah dilewati.
            'route_name' => $routeName ?: $request->path(),
            'aktivitas' => $this->deskripsikan($request, $routeName),
            'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
        ]);
    }

    protected function deskripsikan(Request $request, ?string $routeName): string
    {
        if ($request->is('login') && $request->isMethod('post')) {
            return Auth::check() ? 'Login berhasil' : 'Percobaan login gagal';
        }

        if ($routeName === 'logout') {
            return 'Logout';
        }

        $label = (string) Str::of($routeName ?: $request->path())->replace(['.', '-', '_', '/'], ' ')->title();

        $aksi = match ($request->method()) {
            'POST' => 'Mengirim/menyimpan data',
            'PUT', 'PATCH' => 'Memperbarui data',
            'DELETE' => 'Menghapus data',
            default => 'Membuka halaman',
        };

        return "{$aksi}: {$label}";
    }
}
