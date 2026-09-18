<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterOpd;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PenggunaController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $pengguna = User::query()
            ->with(['pegawai', 'opdDiampu'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $opdList = MasterOpd::query()->orderBy('uraiunor')->get(['id', 'uraiunor']);

        return view('master.pengguna.index', ['pengguna' => $pengguna, 'q' => $q, 'opdList' => $opdList]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:admin,opd,user'],
            'pegawai_id' => ['nullable', 'exists:tb_pegawai_aktif,id'],
            'opd_ids' => ['nullable', 'array'],
            'opd_ids.*' => ['integer', 'exists:tb_opd_aktif,id'],
        ]);

        $pengguna = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'pegawai_id' => $validated['pegawai_id'] ?? null,
            'email_verified_at' => now(),
        ]);

        if ($validated['role'] === 'opd') {
            $pengguna->opdDiampu()->sync($validated['opd_ids'] ?? []);
        }

        return redirect()->route('master.pengguna', $request->only('q'))
            ->with('status', "Pengguna \"{$validated['name']}\" berhasil ditambahkan.");
    }

    public function update(Request $request, User $pengguna): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($pengguna->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'in:admin,opd,user'],
            'pegawai_id' => ['nullable', 'exists:tb_pegawai_aktif,id'],
            'opd_ids' => ['nullable', 'array'],
            'opd_ids.*' => ['integer', 'exists:tb_opd_aktif,id'],
        ]);

        if ($pengguna->id === $request->user()->id && $validated['role'] !== 'admin' && $pengguna->role === 'admin') {
            return redirect()->route('master.pengguna', $request->only('q'))
                ->with('sync_error', 'Anda tidak bisa menurunkan peran akun Anda sendiri.');
        }

        $pengguna->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'pegawai_id' => $validated['pegawai_id'] ?? null,
        ]);

        if (! empty($validated['password'])) {
            $pengguna->password = Hash::make($validated['password']);
        }

        $pengguna->save();

        $pengguna->opdDiampu()->sync($validated['role'] === 'opd' ? ($validated['opd_ids'] ?? []) : []);

        return redirect()->route('master.pengguna', $request->only('q'))
            ->with('status', "Pengguna \"{$pengguna->name}\" berhasil diperbarui.");
    }

    public function destroy(Request $request, User $pengguna): RedirectResponse
    {
        if ($pengguna->id === $request->user()->id) {
            return redirect()->route('master.pengguna', $request->only('q'))
                ->with('sync_error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        if ($pengguna->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return redirect()->route('master.pengguna', $request->only('q'))
                ->with('sync_error', 'Tidak bisa menghapus admin terakhir.');
        }

        $nama = $pengguna->name;
        $pengguna->delete();

        return redirect()->route('master.pengguna', $request->only('q'))
            ->with('status', "Pengguna \"{$nama}\" berhasil dihapus.");
    }
}
