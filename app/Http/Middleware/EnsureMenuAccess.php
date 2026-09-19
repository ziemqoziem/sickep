<?php

namespace App\Http\Middleware;

use App\Models\RoleMenuPermission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMenuAccess
{
    /**
     * Menutup akses langsung lewat URL ke halaman menu yang tidak
     * diizinkan untuk role user tsb -- menyembunyikan di sidebar saja
     * (lihat layouts.sidebar) tidak cukup, karena URL tetap bisa diketik
     * manual. Hanya memeriksa rute yang benar-benar terdaftar sebagai
     * "menu yang bisa diatur" di config/menu.php (bukan setiap rute
     * aplikasi) -- rute aksi turunan (store/update/delete, dsb) sudah
     * punya otorisasinya sendiri masing-masing.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->isAdmin()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if (! $routeName || ! in_array($routeName, $this->ruteBisaDiatur(), true)) {
            return $next($request);
        }

        $diizinkan = RoleMenuPermission::where('role', $user->role)
            ->where('menu_route', $routeName)
            ->exists();

        abort_unless($diizinkan, 403, 'Anda tidak memiliki akses ke halaman ini. Hubungi Admin untuk membuka akses menu ini.');

        return $next($request);
    }

    /**
     * @return array<int, string>
     */
    protected function ruteBisaDiatur(): array
    {
        return collect(config('menu.sections'))
            ->flatMap(fn ($section) => $section['items'] ?? [])
            ->reject(fn ($item) => $item['admin'] ?? false)
            ->pluck('route')
            ->all();
    }
}
