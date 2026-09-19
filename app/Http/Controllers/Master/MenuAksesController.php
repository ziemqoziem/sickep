<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\RoleMenuPermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuAksesController extends Controller
{
    protected const ROLES = ['opd', 'user'];

    public function index(): View
    {
        $granted = [
            'opd' => RoleMenuPermission::where('role', 'opd')->pluck('menu_route')->all(),
            'user' => RoleMenuPermission::where('role', 'user')->pluck('menu_route')->all(),
        ];

        return view('master.akses-menu.index', [
            'sections' => config('menu.sections'),
            'granted' => $granted,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rutePilihan = collect(config('menu.sections'))
            ->flatMap(fn ($section) => $section['items'] ?? [])
            ->reject(fn ($item) => $item['admin'] ?? false)
            ->pluck('route')
            ->all();

        foreach (self::ROLES as $role) {
            // Whitelist ke rute yang benar-benar ada & memang boleh
            // diatur -- mencegah input tidak dikenal ikut tersimpan.
            $dipilih = array_values(array_intersect(
                $request->input($role, []),
                $rutePilihan
            ));

            RoleMenuPermission::where('role', $role)->delete();

            if ($dipilih !== []) {
                RoleMenuPermission::insert(array_map(fn ($route) => [
                    'role' => $role,
                    'menu_route' => $route,
                    'created_at' => now(),
                ], $dipilih));
            }
        }

        return redirect()->route('master.akses-menu')
            ->with('status', 'Hak akses menu berhasil diperbarui.');
    }
}
