<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\JenisCutiAturan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JenisCutiAturanController extends Controller
{
    protected function rules(?int $ignoreId = null): array
    {
        return [
            'kode' => ['required', 'string', 'max:20', Rule::unique('jenis_cuti_aturan', 'kode')->ignore($ignoreId)],
            'nama' => ['required', 'string', 'max:100'],
            'syarat_masa_kerja_bulan' => ['nullable', 'integer', 'min:0'],
            'jatah_hari' => ['nullable', 'integer', 'min:0'],
            'carry_over_hari' => ['nullable', 'integer', 'min:0'],
            'maks_hari' => ['nullable', 'integer', 'min:0'],
            'perlu_dokumen' => ['nullable', 'boolean'],
            'butuh_persetujuan_admin' => ['nullable', 'boolean'],
            'keterangan' => ['nullable', 'string', 'max:2000'],
            'aktif' => ['nullable', 'boolean'],
        ];
    }

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $aturan = JenisCutiAturan::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('kode', 'like', "%{$q}%")
                        ->orWhere('nama', 'like', "%{$q}%");
                });
            })
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        return view('master.jenis-cuti.index', ['aturan' => $aturan, 'q' => $q]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $validated['perlu_dokumen'] = $request->boolean('perlu_dokumen');
        $validated['butuh_persetujuan_admin'] = $request->boolean('butuh_persetujuan_admin');
        $validated['aktif'] = $request->boolean('aktif', true);

        JenisCutiAturan::create($validated);

        return redirect()->route('master.jenis-cuti', $request->only('q'))
            ->with('status', "Aturan \"{$validated['nama']}\" berhasil ditambahkan.");
    }

    public function update(Request $request, JenisCutiAturan $jenisCuti): RedirectResponse
    {
        $validated = $request->validate($this->rules($jenisCuti->id));
        $validated['perlu_dokumen'] = $request->boolean('perlu_dokumen');
        $validated['butuh_persetujuan_admin'] = $request->boolean('butuh_persetujuan_admin');
        $validated['aktif'] = $request->boolean('aktif');

        $jenisCuti->update($validated);

        return redirect()->route('master.jenis-cuti', $request->only('q'))
            ->with('status', "Aturan \"{$jenisCuti->nama}\" berhasil diperbarui.");
    }
}
