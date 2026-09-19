<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengumumanController extends Controller
{
    protected function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:200'],
            'isi' => ['required', 'string', 'max:5000'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'aktif' => ['nullable', 'boolean'],
        ];
    }

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $pengumuman = Pengumuman::query()
            ->when($q !== '', fn ($query) => $query->where('judul', 'like', "%{$q}%"))
            ->orderByDesc('tanggal_mulai')
            ->paginate(15)
            ->withQueryString();

        return view('master.pengumuman.index', ['pengumuman' => $pengumuman, 'q' => $q]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $validated['aktif'] = $request->boolean('aktif', true);
        $validated['dibuat_oleh'] = $request->user()->id;

        Pengumuman::create($validated);

        return redirect()->route('master.pengumuman', $request->only('q'))
            ->with('status', "Pengumuman \"{$validated['judul']}\" berhasil ditambahkan.");
    }

    public function update(Request $request, Pengumuman $pengumuman): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $validated['aktif'] = $request->boolean('aktif');

        $pengumuman->update($validated);

        return redirect()->route('master.pengumuman', $request->only('q'))
            ->with('status', "Pengumuman \"{$pengumuman->judul}\" berhasil diperbarui.");
    }

    public function destroy(Request $request, Pengumuman $pengumuman): RedirectResponse
    {
        $judul = $pengumuman->judul;
        $pengumuman->delete();

        return redirect()->route('master.pengumuman', $request->only('q'))
            ->with('status', "Pengumuman \"{$judul}\" berhasil dihapus.");
    }
}
