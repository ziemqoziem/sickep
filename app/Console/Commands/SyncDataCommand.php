<?php

namespace App\Console\Commands;

use App\Models\SyncLog;
use App\Services\MdbConnection;
use App\Services\SyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Throwable;

class SyncDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:data
        {entity=all : tkodecuti|kode_formasijabatan|sysdb_instansi|sysdb_wkguni|sysdb_wkgunitampungan|tprofilunitkerja|sysdb_pns|sysdb_pnsdetail|sysdb_pnskpg|riwayatcuti|all}
        {--full : Abaikan watermark, scan ulang seluruh data dari sumber (bukan cuma yang berubah)}
        {--prune : Hapus baris lokal yang sudah tidak ada di sumber. Hanya untuk riwayatcuti, otomatis memaksa --full}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronkan tabel-tabel sumber (SIMABSARA2017) ke database aplikasi (mirror 1:1)';

    /**
     * Urutan sinkronisasi: tabel referensi/lookup dulu, baru tabel
     * pegawai, baru riwayatcuti (paling besar) terakhir.
     */
    protected array $order = [
        'tkodecuti',
        'kode_formasijabatan',
        'sysdb_instansi',
        'sysdb_wkguni',
        'sysdb_wkgunitampungan',
        'tprofilunitkerja',
        'sysdb_pns',
        'sysdb_pnsdetail',
        'sysdb_pnskpg',
        'riwayatcuti',
    ];

    public function handle(): int
    {
        $entity = $this->argument('entity');

        if ($entity !== 'all' && ! in_array($entity, $this->order, true)) {
            $this->error("Entity tidak dikenal: {$entity}");

            return self::FAILURE;
        }

        try {
            $service = new SyncService(MdbConnection::connect());
        } catch (Throwable $e) {
            $this->error('Gagal konek ke database sumber: '.$e->getMessage());

            foreach ($this->entitiesToRun($entity) as $key) {
                $this->logFailure($key, $e->getMessage());
            }

            return self::FAILURE;
        }

        $prune = (bool) $this->option('prune');
        $full = (bool) $this->option('full') || $prune;
        $incremental = ! $full;

        if ($prune && $entity !== 'all' && $entity !== 'riwayatcuti') {
            $this->warn('--prune hanya berlaku untuk riwayatcuti, diabaikan untuk entity lain.');
        }

        $exitCode = self::SUCCESS;

        foreach ($this->entitiesToRun($entity) as $key) {
            $mode = match (true) {
                $key === 'riwayatcuti' && $prune => ' (full scan + hapus data hilang)',
                $key === 'riwayatcuti' && $full => ' (full scan)',
                $key === 'sysdb_pnskpg' && $full => ' (full scan)',
                $key === 'riwayatcuti', $key === 'sysdb_pnskpg' => ' (incremental)',
                default => '',
            };

            $this->info("Sinkronisasi {$key}{$mode}...");

            $log = SyncLog::create([
                'entity' => $key,
                'label' => SyncLog::entities()[$key],
                'status' => 'running',
                'started_at' => now(),
            ]);

            try {
                $count = match ($key) {
                    'tkodecuti' => $service->syncTkodecuti(),
                    'kode_formasijabatan' => $service->syncKodeFormasijabatan(),
                    'sysdb_instansi' => $service->syncSysdbInstansi(),
                    'sysdb_wkguni' => $service->syncSysdbWkguni(),
                    'sysdb_wkgunitampungan' => $service->syncSysdbWkguniTampungan(),
                    'tprofilunitkerja' => $service->syncTprofilunitkerja(),
                    'sysdb_pns' => $service->syncSysdbPns(),
                    'sysdb_pnsdetail' => $service->syncSysdbPnsdetail(),
                    'sysdb_pnskpg' => $service->syncSysdbPnskpg($incremental),
                    'riwayatcuti' => $service->syncRiwayatcuti($incremental, $key === 'riwayatcuti' && $prune),
                };

                $log->update([
                    'status' => 'success',
                    'records_synced' => $count,
                    'finished_at' => now(),
                    'message' => null,
                ]);

                $this->info("  selesai, {$count} baris.");
            } catch (Throwable $e) {
                $log->update([
                    'status' => 'failed',
                    'finished_at' => now(),
                    'message' => Str::limit($e->getMessage(), 2000),
                ]);

                $this->error('  gagal: '.$e->getMessage());
                $exitCode = self::FAILURE;
            }
        }

        return $exitCode;
    }

    /**
     * @return array<int, string>
     */
    protected function entitiesToRun(string $entity): array
    {
        return $entity === 'all' ? $this->order : [$entity];
    }

    protected function logFailure(string $entity, string $message): void
    {
        SyncLog::create([
            'entity' => $entity,
            'label' => SyncLog::entities()[$entity],
            'status' => 'failed',
            'started_at' => now(),
            'finished_at' => now(),
            'message' => Str::limit($message, 2000),
        ]);
    }
}
