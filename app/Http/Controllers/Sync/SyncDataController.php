<?php

namespace App\Http\Controllers\Sync;

use App\Http\Controllers\Controller;
use App\Models\SyncLog;
use App\Models\SyncRun;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class SyncDataController extends Controller
{
    /**
     * Entitas yang punya kolom modidatetime di sumber, sehingga mendukung
     * incremental sync (lihat SyncService::resolveWatermark).
     */
    protected const INCREMENTAL_ENTITIES = ['riwayatcuti', 'sysdb_pnskpg'];

    public function index(): View
    {
        $latestLogs = SyncLog::query()
            ->orderByDesc('id')
            ->get()
            ->unique('entity');

        $syncRuns = SyncRun::query()->get()->keyBy('entity');

        $rows = collect(SyncLog::entities())->map(function (string $label, string $entity) use ($latestLogs, $syncRuns) {
            $log = $latestLogs->firstWhere('entity', $entity);
            $run = $syncRuns->get($entity);

            return [
                'entity' => $entity,
                'label' => $label,
                'status' => $log->status ?? 'belum_pernah',
                'records_synced' => $log->records_synced ?? null,
                'finished_at' => $log->finished_at ?? null,
                'message' => $log->message ?? null,
                'incremental' => in_array($entity, self::INCREMENTAL_ENTITIES, true),
                'watermark' => $run?->last_watermark,
            ];
        })->values();

        return view('sync.index', ['rows' => $rows]);
    }

    public function run(Request $request, string $entity): RedirectResponse
    {
        if ($entity !== 'all' && ! array_key_exists($entity, SyncLog::entities())) {
            abort(404);
        }

        set_time_limit(0);

        $options = [
            'entity' => $entity,
            '--full' => $request->boolean('full'),
            '--prune' => $request->boolean('prune'),
        ];

        $exitCode = Artisan::call('sync:data', $options);

        if ($exitCode !== 0) {
            return redirect()
                ->route('sync-data')
                ->with('sync_error', 'Sinkronisasi selesai dengan error. Lihat kolom status/pesan pada tabel di bawah.');
        }

        return redirect()
            ->route('sync-data')
            ->with('sync_success', 'Sinkronisasi berhasil dijalankan.');
    }
}
