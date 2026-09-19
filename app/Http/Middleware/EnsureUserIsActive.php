<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Menjaga sesi yang sudah terlanjur login tapi akunnya dinonaktifkan
     * setelahnya (manual atau lewat trigger nonaktifkan pegawai) -- tanpa
     * ini, blokir di LoginRequest saja tidak cukup karena sesi lama masih
     * tetap jalan sampai logout/expired sendiri.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->aktif) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['username' => 'Akun Anda telah dinonaktifkan. Hubungi Admin untuk informasi lebih lanjut.']);
        }

        return $next($request);
    }
}
