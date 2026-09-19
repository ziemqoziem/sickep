<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    protected const PER_PAGE_OPTIONS = [10, 15, 20, 25, 50];

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $userId = $request->query('user_id', '');
        $method = $request->query('method', '');
        $dari = $request->query('dari', '');
        $sampai = $request->query('sampai', '');
        $perPage = (int) $request->query('per_page', 20);

        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = 20;
        }

        $logs = ActivityLog::query()
            ->with('user:id,name,username')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('aktivitas', 'like', "%{$q}%")
                        ->orWhere('nama_pengguna', 'like', "%{$q}%")
                        ->orWhere('ip_address', 'like', "%{$q}%")
                        ->orWhere('url', 'like', "%{$q}%");
                });
            })
            ->when($userId !== '', fn ($query) => $userId === '0'
                ? $query->whereNull('user_id')
                : $query->where('user_id', $userId))
            ->when(in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], true), fn ($query) => $query->where('method', $method))
            ->when($dari !== '', fn ($query) => $query->whereDate('created_at', '>=', $dari))
            ->when($sampai !== '', fn ($query) => $query->whereDate('created_at', '<=', $sampai))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        $userList = User::orderBy('name')->get(['id', 'name']);

        $summary = [
            'hari_ini' => ActivityLog::whereDate('created_at', today())->count(),
            'tujuh_hari' => ActivityLog::where('created_at', '>=', now()->subDays(7))->count(),
            'pengguna_aktif_hari_ini' => ActivityLog::whereDate('created_at', today())->whereNotNull('user_id')->distinct('user_id')->count('user_id'),
            'login_gagal_hari_ini' => ActivityLog::whereDate('created_at', today())->where('aktivitas', 'Percobaan login gagal')->count(),
        ];

        return view('master.log-aktivitas.index', [
            'logs' => $logs,
            'q' => $q,
            'userId' => $userId,
            'method' => $method,
            'dari' => $dari,
            'sampai' => $sampai,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'userList' => $userList,
            'summary' => $summary,
        ]);
    }
}
