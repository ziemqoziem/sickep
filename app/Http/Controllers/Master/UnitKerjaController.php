<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterOpd;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitKerjaController extends Controller
{
    protected function rules(?int $ignoreId = null): array
    {
        return [
            'idunor' => ['required', 'string', 'max:30', 'unique:tb_opd_aktif,idunor'.($ignoreId ? ",{$ignoreId}" : '')],
            'uraiunor' => ['required', 'string', 'max:255'],
            'akronim' => ['nullable', 'string', 'max:50'],
            'nama_jabatan' => ['nullable', 'string', 'max:200'],
            'nama_kepala' => ['nullable', 'string', 'max:150'],
            'nip_kepala' => ['nullable', 'string', 'max:18'],
            'pangkat_kepala' => ['nullable', 'string', 'max:50'],
            'golongan_kepala' => ['nullable', 'string', 'max:10'],
            'status_jabatan' => ['nullable', 'in:Definitif,Pelaksana Tugas (Plt),Pelaksana Harian (Plh)'],
        ];
    }

    protected const PER_PAGE_OPTIONS = [10, 15, 20, 25, 30, 50];

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $perPage = (int) $request->query('per_page', 15);
        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = 15;
        }

        $unitKerja = MasterOpd::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('idunor', 'like', "%{$q}%")
                        ->orWhere('uraiunor', 'like', "%{$q}%")
                        ->orWhere('akronim', 'like', "%{$q}%");
                });
            })
            ->withCount('pegawai')
            ->orderBy('idunor')
            ->paginate($perPage)
            ->withQueryString();

        return view('master.unit-kerja.index', [
            'unitKerja' => $unitKerja,
            'q' => $q,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        MasterOpd::create($validated);

        return redirect()->route('master.unit-kerja', $request->only(['q', 'per_page']))
            ->with('status', "Unit kerja \"{$validated['uraiunor']}\" berhasil ditambahkan.");
    }

    public function update(Request $request, MasterOpd $unitKerja): RedirectResponse
    {
        $validated = $request->validate($this->rules($unitKerja->id));

        $unitKerja->update($validated);

        return redirect()->route('master.unit-kerja', $request->only(['q', 'per_page']))
            ->with('status', "Unit kerja \"{$validated['uraiunor']}\" berhasil diperbarui.");
    }

    public function destroy(Request $request, MasterOpd $unitKerja): RedirectResponse
    {
        try {
            $nama = $unitKerja->uraiunor;
            $unitKerja->delete();

            return redirect()->route('master.unit-kerja', $request->only(['q', 'per_page']))
                ->with('status', "Unit kerja \"{$nama}\" berhasil dihapus.");
        } catch (QueryException $e) {
            return redirect()->route('master.unit-kerja', $request->only(['q', 'per_page']))
                ->with('sync_error', "Unit kerja \"{$unitKerja->uraiunor}\" tidak bisa dihapus karena masih memiliki data pegawai terkait.");
        }
    }
}
