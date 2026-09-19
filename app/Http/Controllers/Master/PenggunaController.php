<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterOpd;
use App\Models\MasterPegawai;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PenggunaController extends Controller
{
    protected const PER_PAGE_OPTIONS = [10, 15, 20, 25, 50];

    protected const DEFAULT_PASSWORD = 'sickep';

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $role = $request->query('role', '');
        $perPage = (int) $request->query('per_page', 15);

        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = 15;
        }

        $pengguna = User::query()
            ->with(['pegawai', 'opdDiampu'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('username', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhereHas('pegawai', fn ($p) => $p->where('nip', 'like', "%{$q}%"));
                });
            })
            ->when(in_array($role, ['admin', 'opd', 'user'], true), fn ($query) => $query->where('role', $role))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        $opdList = MasterOpd::query()->orderBy('uraiunor')->get(['id', 'uraiunor', 'idunor']);

        return view('master.pengguna.index', [
            'pengguna' => $pengguna,
            'q' => $q,
            'role' => $role,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'opdList' => $opdList,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:admin,opd,user'],
            'pegawai_id' => ['nullable', 'exists:tb_pegawai_aktif,id'],
            'opd_ids' => ['nullable', 'array'],
            'opd_ids.*' => ['integer', 'exists:tb_opd_aktif,id'],
        ]);

        $email = $validated['email'] ?? null;

        $pengguna = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $email,
            'no_hp' => $validated['no_hp'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'pegawai_id' => $validated['pegawai_id'] ?? null,
            'email_verified_at' => $email ? now() : null,
        ]);

        if ($validated['role'] === 'opd') {
            $pengguna->opdDiampu()->sync($validated['opd_ids'] ?? []);
        }

        return redirect()->route('master.pengguna', $request->only('q', 'role', 'per_page'))
            ->with('status', "Pengguna \"{$validated['name']}\" berhasil ditambahkan.");
    }

    public function update(Request $request, User $pengguna): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($pengguna->id)],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($pengguna->id)],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'in:admin,opd,user'],
            'pegawai_id' => ['nullable', 'exists:tb_pegawai_aktif,id'],
            'opd_ids' => ['nullable', 'array'],
            'opd_ids.*' => ['integer', 'exists:tb_opd_aktif,id'],
        ]);

        if ($pengguna->id === $request->user()->id && $validated['role'] !== 'admin' && $pengguna->role === 'admin') {
            return redirect()->route('master.pengguna', $request->only('q', 'role', 'per_page'))
                ->with('sync_error', 'Anda tidak bisa menurunkan peran akun Anda sendiri.');
        }

        $emailBerubah = $pengguna->email !== ($validated['email'] ?? null);

        $pengguna->fill([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'] ?? null,
            'no_hp' => $validated['no_hp'] ?? null,
            'role' => $validated['role'],
            'pegawai_id' => $validated['pegawai_id'] ?? null,
        ]);

        if ($emailBerubah) {
            $pengguna->email_verified_at = $pengguna->email ? now() : null;
        }

        if (! empty($validated['password'])) {
            $pengguna->password = Hash::make($validated['password']);
        }

        $pengguna->save();

        $pengguna->opdDiampu()->sync($validated['role'] === 'opd' ? ($validated['opd_ids'] ?? []) : []);

        return redirect()->route('master.pengguna', $request->only('q', 'role', 'per_page'))
            ->with('status', "Pengguna \"{$pengguna->name}\" berhasil diperbarui.");
    }

    /**
     * Generate akun login otomatis -- username murni teks (bukan email),
     * dari NIP pegawai (role "user") atau idunor OPD (role "opd"),
     * dengan password default "sickep". Mendukung mode single (satu
     * pegawai/OPD terpilih) maupun bulk ("semua OPD" untuk admin OPD,
     * atau "semua pegawai di satu OPD" untuk user).
     */
    public function generate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:user,opd'],
            'mode' => ['required', 'in:single,all'],
        ]);

        if ($validated['type'] === 'user' && $validated['mode'] === 'single') {
            $request->validate(['pegawai_id' => ['required', 'exists:tb_pegawai_aktif,id']]);

            return $this->generateUserSingle($request, (int) $request->input('pegawai_id'));
        }

        if ($validated['type'] === 'user') {
            $request->validate(['opd_id' => ['required', 'exists:tb_opd_aktif,id']]);

            return $this->generateUserAllInOpd($request, (int) $request->input('opd_id'));
        }

        if ($validated['mode'] === 'single') {
            $request->validate(['opd_id' => ['required', 'exists:tb_opd_aktif,id']]);

            return $this->generateOpdSingle($request, (int) $request->input('opd_id'));
        }

        return $this->generateOpdAll($request);
    }

    protected function generateUserSingle(Request $request, int $pegawaiId): RedirectResponse
    {
        $pegawai = MasterPegawai::findOrFail($pegawaiId);

        if ($pegawai->user) {
            return redirect()->route('master.pengguna', $request->only('q', 'role', 'per_page'))
                ->with('sync_error', "Pegawai \"{$pegawai->namaLengkap()}\" sudah memiliki akun pengguna.");
        }

        $username = $this->usernameTersedia($pegawai->nip);

        User::create([
            'name' => $pegawai->namaLengkap(),
            'username' => $username,
            'password' => Hash::make(self::DEFAULT_PASSWORD),
            'role' => 'user',
            'pegawai_id' => $pegawai->id,
        ]);

        return redirect()->route('master.pengguna', $request->only('q', 'role', 'per_page'))
            ->with('status', "Akun untuk \"{$pegawai->namaLengkap()}\" berhasil dibuat. Username: {$username}, Password: ".self::DEFAULT_PASSWORD);
    }

    protected function generateOpdSingle(Request $request, int $opdId): RedirectResponse
    {
        $opd = MasterOpd::findOrFail($opdId);

        $username = $this->usernameTersedia(Str::lower($opd->idunor));

        $pengguna = User::create([
            'name' => "Admin OPD - {$opd->uraiunor}",
            'username' => $username,
            'password' => Hash::make(self::DEFAULT_PASSWORD),
            'role' => 'opd',
        ]);

        $pengguna->opdDiampu()->sync([$opd->id]);

        return redirect()->route('master.pengguna', $request->only('q', 'role', 'per_page'))
            ->with('status', "Akun Admin OPD untuk \"{$opd->uraiunor}\" berhasil dibuat. Username: {$username}, Password: ".self::DEFAULT_PASSWORD);
    }

    /**
     * Bulk-generate akun "user" untuk seluruh pegawai di satu OPD yang
     * belum memiliki akun. Password di-hash sekali saja lalu dipakai
     * ulang (bcrypt sengaja lambat -- meng-hash per baris untuk ribuan
     * pegawai akan membuat request timeout), dan insert dilakukan lewat
     * query builder mentah (bukan Eloquent::create per baris) supaya
     * cukup cepat untuk OPD besar (ribuan pegawai).
     */
    protected function generateUserAllInOpd(Request $request, int $opdId): RedirectResponse
    {
        set_time_limit(300);

        $opd = MasterOpd::findOrFail($opdId);

        $pegawaiList = MasterPegawai::where('opd_id', $opdId)
            ->doesntHave('user')
            ->get(['id', 'nip', 'nama', 'gelar_depan', 'gelar_belakang']);

        if ($pegawaiList->isEmpty()) {
            return redirect()->route('master.pengguna', $request->only('q', 'role', 'per_page'))
                ->with('sync_error', "Semua pegawai di OPD \"{$opd->uraiunor}\" sudah memiliki akun pengguna.");
        }

        $hashedPassword = Hash::make(self::DEFAULT_PASSWORD);
        $existing = User::pluck('username')->flip()->all();
        $now = now();
        $rows = [];

        foreach ($pegawaiList as $pegawai) {
            $rows[] = [
                'name' => $pegawai->namaLengkap(),
                'username' => $this->usernameTersediaDalamSet($pegawai->nip, $existing),
                'password' => $hashedPassword,
                'role' => 'user',
                'pegawai_id' => $pegawai->id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            User::insert($chunk);
        }

        return redirect()->route('master.pengguna', $request->only('q', 'role', 'per_page'))
            ->with('status', count($rows)." akun pengguna berhasil dibuat untuk pegawai di OPD \"{$opd->uraiunor}\" (password default: ".self::DEFAULT_PASSWORD.').');
    }

    /**
     * Bulk-generate akun "opd" (Admin OPD) untuk setiap OPD yang belum
     * punya admin sama sekali.
     */
    protected function generateOpdAll(Request $request): RedirectResponse
    {
        set_time_limit(120);

        $opdList = MasterOpd::query()
            ->whereDoesntHave('admins')
            ->orderBy('uraiunor')
            ->get(['id', 'uraiunor', 'idunor']);

        if ($opdList->isEmpty()) {
            return redirect()->route('master.pengguna', $request->only('q', 'role', 'per_page'))
                ->with('sync_error', 'Semua OPD sudah memiliki akun Admin OPD.');
        }

        $hashedPassword = Hash::make(self::DEFAULT_PASSWORD);
        $existing = User::pluck('username')->flip()->all();
        $created = 0;

        foreach ($opdList as $opd) {
            $username = $this->usernameTersediaDalamSet(Str::lower($opd->idunor), $existing);

            $pengguna = User::create([
                'name' => "Admin OPD - {$opd->uraiunor}",
                'username' => $username,
                'password' => $hashedPassword,
                'role' => 'opd',
            ]);

            $pengguna->opdDiampu()->attach($opd->id);
            $created++;
        }

        return redirect()->route('master.pengguna', $request->only('q', 'role', 'per_page'))
            ->with('status', "{$created} akun Admin OPD berhasil dibuat (password default: ".self::DEFAULT_PASSWORD.').');
    }

    /**
     * Pastikan username unik -- kalau sudah dipakai (mis. NIP/kode OPD
     * sama pernah dibuat lalu diganti manual), tambahkan suffix angka.
     */
    protected function usernameTersedia(string $username): string
    {
        if (! User::where('username', $username)->exists()) {
            return $username;
        }

        $i = 2;
        do {
            $candidate = "{$username}-{$i}";
            $i++;
        } while (User::where('username', $candidate)->exists());

        return $candidate;
    }

    /**
     * Varian in-memory dari usernameTersedia() untuk proses bulk --
     * menghindari satu query SELECT per baris saat generate ribuan akun
     * sekaligus. $existing juga langsung ditandai terpakai (by reference)
     * supaya baris berikutnya dalam batch yang sama tidak bentrok.
     */
    protected function usernameTersediaDalamSet(string $username, array &$existing): string
    {
        $candidate = $username;
        $i = 2;

        while (isset($existing[$candidate])) {
            $candidate = "{$username}-{$i}";
            $i++;
        }

        $existing[$candidate] = true;

        return $candidate;
    }

    public function nonaktifkan(Request $request, User $pengguna): RedirectResponse
    {
        if ($pengguna->id === $request->user()->id) {
            return redirect()->route('master.pengguna', $request->only('q', 'role', 'per_page'))
                ->with('sync_error', 'Anda tidak bisa menonaktifkan akun Anda sendiri.');
        }

        if ($pengguna->role === 'admin' && User::where('role', 'admin')->where('aktif', true)->count() <= 1) {
            return redirect()->route('master.pengguna', $request->only('q', 'role', 'per_page'))
                ->with('sync_error', 'Tidak bisa menonaktifkan admin aktif terakhir.');
        }

        $pengguna->update(['aktif' => false]);

        return redirect()->route('master.pengguna', $request->only('q', 'role', 'per_page'))
            ->with('status', "Pengguna \"{$pengguna->name}\" berhasil dinonaktifkan. Akun ini tidak bisa login lagi.");
    }

    public function aktifkan(Request $request, User $pengguna): RedirectResponse
    {
        $pengguna->update(['aktif' => true]);

        return redirect()->route('master.pengguna', $request->only('q', 'role', 'per_page'))
            ->with('status', "Pengguna \"{$pengguna->name}\" berhasil diaktifkan kembali.");
    }

    public function destroy(Request $request, User $pengguna): RedirectResponse
    {
        if ($pengguna->id === $request->user()->id) {
            return redirect()->route('master.pengguna', $request->only('q', 'role', 'per_page'))
                ->with('sync_error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        if ($pengguna->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return redirect()->route('master.pengguna', $request->only('q', 'role', 'per_page'))
                ->with('sync_error', 'Tidak bisa menghapus admin terakhir.');
        }

        $nama = $pengguna->name;
        $pengguna->delete();

        return redirect()->route('master.pengguna', $request->only('q', 'role', 'per_page'))
            ->with('status', "Pengguna \"{$nama}\" berhasil dihapus.");
    }
}
