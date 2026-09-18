<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterOpd;
use App\Models\MasterPegawai;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MasterPegawaiController extends Controller
{
    protected const GOLONGAN_OPTIONS = [
        'I/a', 'I/b', 'I/c', 'I/d',
        'II/a', 'II/b', 'II/c', 'II/d',
        'III/a', 'III/b', 'III/c', 'III/d',
        'IV/a', 'IV/b', 'IV/c', 'IV/d', 'IV/e',
    ];

    protected function rules(?int $ignoreId = null): array
    {
        return [
            'nip' => ['required', 'string', 'max:18', Rule::unique('tb_pegawai_aktif', 'nip')->ignore($ignoreId)],
            'nama' => ['required', 'string', 'max:150'],
            'gelar_depan' => ['nullable', 'string', 'max:30'],
            'gelar_belakang' => ['nullable', 'string', 'max:100'],
            'status_kepegawaian' => ['required', 'in:PNS,PPPK,PPPK Paruh Waktu,Calon PNS'],
            'golongan' => ['nullable', 'in:'.implode(',', self::GOLONGAN_OPTIONS)],
            'pangkat' => ['nullable', 'string', 'max:100'],
            'pendidikan' => ['nullable', 'string', 'max:150'],
            'jabatan' => ['nullable', 'string', 'max:150'],
            'jenis_jabatan' => ['nullable', 'in:Jabatan Struktural,Jabatan Fungsional,Jabatan Pelaksana'],
            'unit_kerja' => ['nullable', 'string', 'max:255'],
            'opd_id' => ['required', 'exists:tb_opd_aktif,id'],
            'status_aktif' => ['required', 'in:Aktif,Tidak Aktif'],
        ];
    }

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $status = (array) $request->query('status', []);

        $base = MasterPegawai::query();

        $summary = [
            'total' => (clone $base)->count(),
            'aktif' => (clone $base)->where('status_aktif', 'Aktif')->count(),
            'tidak_aktif' => (clone $base)->where('status_aktif', 'Tidak Aktif')->count(),
        ];

        $pegawai = MasterPegawai::query()
            ->with('opd')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nama', 'like', "%{$q}%")
                        ->orWhere('nip', 'like', "%{$q}%")
                        ->orWhere('unit_kerja', 'like', "%{$q}%");
                });
            })
            ->when(! empty($status), fn ($query) => $query->whereIn('status_aktif', $status))
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        $opdList = MasterOpd::query()->orderBy('uraiunor')->get(['id', 'uraiunor']);

        return view('master.pegawai.index', [
            'pegawai' => $pegawai,
            'q' => $q,
            'status' => $status,
            'summary' => $summary,
            'opdList' => $opdList,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        MasterPegawai::create($validated);

        return redirect()->route('master.pegawai', $request->only(['q', 'status']))
            ->with('status', "Pegawai \"{$validated['nama']}\" berhasil ditambahkan.");
    }

    public function update(Request $request, MasterPegawai $pegawai): RedirectResponse
    {
        $validated = $request->validate($this->rules($pegawai->id));

        // NIP dan nama dikunci di form edit (lihat master/pegawai/index.blade.php)
        // -- diabaikan di sini juga supaya tidak bisa diubah lewat request mentah.
        unset($validated['nip'], $validated['nama']);

        $pegawai->update($validated);

        return redirect()->route('master.pegawai', $request->only(['q', 'status']))
            ->with('status', "Pegawai \"{$pegawai->nama}\" berhasil diperbarui.");
    }

    /**
     * Dipakai oleh pencarian kepala unit di form Unit Kerja (lihat
     * master/unit-kerja/index.blade.php) -- bukan untuk relasi FK
     * (kepala_pegawai_id rusak, lihat catatan di MasterOpd), hanya untuk
     * mengisi otomatis kolom teks nama/nip/pangkat/golongan kepala.
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $results = MasterPegawai::query()
            ->where(function ($query) use ($q) {
                $query->where('nama', 'like', "%{$q}%")
                    ->orWhere('nip', 'like', "%{$q}%");
            })
            ->orderBy('nama')
            ->limit(15)
            ->get();

        return response()->json($results->map(fn (MasterPegawai $p) => [
            'nip' => $p->nip,
            'nama' => $p->namaLengkap(),
            'pangkat' => $p->pangkat,
            'golongan' => $p->golongan,
            'jabatan' => $p->jabatan,
        ])->values());
    }
}
