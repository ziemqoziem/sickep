<?php

namespace App\Http\Controllers\Cuti;

use App\Http\Controllers\Controller;
use App\Models\CutiPengajuan;
use App\Models\CutiPengajuanLog;
use App\Models\CutiPengajuanTahap;
use App\Models\JenisCutiAturan;
use App\Services\AtasanLangsungService;
use App\Services\CutiTahapKeputusanService;
use App\Services\CutiValidationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CutiPengajuanController extends Controller
{
    protected const PER_PAGE = 10;

    public function __construct(
        protected AtasanLangsungService $atasanLangsungService,
        protected CutiValidationService $validationService,
        protected CutiTahapKeputusanService $keputusanService,
    ) {}

    public function create(Request $request): View|RedirectResponse
    {
        $pegawai = $request->user()->pegawai;

        if (! $pegawai) {
            return redirect()->route('cuti-baru.riwayat')
                ->with('sync_error', 'Akun Anda belum ditautkan ke data pegawai. Hubungi Admin untuk menautkan akun.');
        }

        $jenisCutiList = JenisCutiAturan::query()->where('aktif', true)->orderBy('nama')->get();
        $kandidatAtasan = $this->atasanLangsungService->kandidat($pegawai);

        return view('cuti-baru.ajukan', [
            'jenisCutiList' => $jenisCutiList,
            'kandidatAtasan' => $kandidatAtasan,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $pegawai = $request->user()->pegawai;

        if (! $pegawai) {
            return redirect()->route('cuti-baru.riwayat')
                ->with('sync_error', 'Akun Anda belum ditautkan ke data pegawai. Hubungi Admin untuk menautkan akun.');
        }

        $validated = $request->validate([
            'jenis_cuti_aturan_id' => ['required', 'exists:jenis_cuti_aturan,id'],
            'atasan_langsung_pegawai_id' => ['nullable', 'exists:tb_pegawai_aktif,id'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'alasan' => ['required', 'string', 'max:2000'],
            'alamat_selama_cuti' => ['nullable', 'string', 'max:255'],
            'telepon_selama_cuti' => ['nullable', 'string', 'max:30'],
            'keterangan_anak_ke' => ['nullable', 'integer', 'min:1'],
            'lampiran' => ['nullable', 'array'],
            'lampiran.*' => ['file', 'max:5120'],
        ]);

        $jenis = JenisCutiAturan::findOrFail($validated['jenis_cuti_aturan_id']);
        $mulai = Carbon::parse($validated['tanggal_mulai']);
        $selesai = ! empty($validated['tanggal_selesai']) ? Carbon::parse($validated['tanggal_selesai']) : null;

        $errors = $this->validationService->validate($pegawai, $jenis, $mulai, $selesai);

        if (! empty($errors)) {
            return back()->withInput()->withErrors(['validasi' => $errors]);
        }

        $atasan = null;
        if (! empty($validated['atasan_langsung_pegawai_id'])) {
            $kandidat = $this->atasanLangsungService->kandidat($pegawai);
            $atasan = $kandidat->firstWhere('id', (int) $validated['atasan_langsung_pegawai_id']);
        }

        $lamaHari = $mulai->diffInDays($selesai ?? $mulai) + 1;
        $jumlahTahap = ($atasan ? 1 : 0) + 1 + ($jenis->butuh_persetujuan_admin ? 1 : 0);
        // Minimal 1 tahap (kepala unit kerja) selalu ada; kalau atasan langsung
        // tidak dipilih/tidak tersedia, jenjang itu dilewati (lihat bawah).
        if ($jumlahTahap < 1) {
            $jumlahTahap = 1;
        }

        $nomorPengajuan = $this->nomorPengajuanBerikutnya($mulai->year);

        $pengajuan = DB::transaction(function () use ($request, $pegawai, $jenis, $mulai, $selesai, $validated, $atasan, $lamaHari, $jumlahTahap, $nomorPengajuan) {
            $pengajuan = CutiPengajuan::create([
                'nomor_pengajuan' => $nomorPengajuan,
                'pegawai_id' => $pegawai->id,
                'atasan_langsung_pegawai_id' => $atasan?->id,
                'jenis_cuti_aturan_id' => $jenis->id,
                'tanggal_mulai' => $mulai->toDateString(),
                'tanggal_selesai' => $selesai?->toDateString(),
                'lama_hari' => $lamaHari,
                'alasan' => $validated['alasan'],
                'alamat_selama_cuti' => $validated['alamat_selama_cuti'] ?? null,
                'telepon_selama_cuti' => $validated['telepon_selama_cuti'] ?? null,
                'keterangan_anak_ke' => $validated['keterangan_anak_ke'] ?? null,
                'status' => 'diajukan',
                'jumlah_tahap' => $jumlahTahap,
                'qr_token' => (string) Str::uuid(),
                'dibuat_oleh' => $request->user()->id,
            ]);

            $urutan = 1;

            $pengajuan->tahap()->create([
                'urutan' => $urutan++,
                'jenjang' => 'atasan_langsung',
                'status' => $atasan ? 'menunggu' : 'dilewati',
            ]);

            $pengajuan->tahap()->create([
                'urutan' => $urutan++,
                'jenjang' => 'kepala_unit_kerja',
                'status' => 'menunggu',
            ]);

            if ($jenis->butuh_persetujuan_admin) {
                $pengajuan->tahap()->create([
                    'urutan' => $urutan++,
                    'jenjang' => 'admin',
                    'status' => 'menunggu',
                ]);
            }

            foreach ($request->file('lampiran', []) as $file) {
                $path = $file->store('cuti-lampiran', 'public');

                $pengajuan->lampiran()->create([
                    'nama_dokumen' => $file->getClientOriginalName(),
                    'path_file' => $path,
                    'diunggah_oleh' => $request->user()->id,
                ]);
            }

            CutiPengajuanLog::create([
                'cuti_pengajuan_id' => $pengajuan->id,
                'status_sebelum' => null,
                'status_sesudah' => 'diajukan',
                'oleh' => $request->user()->id,
                'catatan' => 'Pengajuan dibuat.',
            ]);

            return $pengajuan;
        });

        return redirect()->route('cuti-baru.riwayat')
            ->with('status', "Pengajuan {$pengajuan->nomor_pengajuan} berhasil dikirim.");
    }

    protected function nomorPengajuanBerikutnya(int $tahun): string
    {
        $urutan = CutiPengajuan::query()
            ->whereYear('created_at', $tahun)
            ->count() + 1;

        return sprintf('CT-%d-%06d', $tahun, $urutan);
    }

    public function myIndex(Request $request): View
    {
        $pegawai = $request->user()->pegawai;

        $riwayat = CutiPengajuan::query()
            ->with(['jenisCutiAturan', 'tahap'])
            ->when($pegawai, fn ($q) => $q->where('pegawai_id', $pegawai->id), fn ($q) => $q->whereRaw('1 = 0'))
            ->orderByDesc('created_at')
            ->paginate(static::PER_PAGE);

        $saldoTahunan = $pegawai
            ? \App\Models\CutiSaldoTahunan::where('pegawai_id', $pegawai->id)->where('tahun', now()->year)->first()
            : null;

        return view('cuti-baru.riwayat', [
            'pegawai' => $pegawai,
            'riwayat' => $riwayat,
            'sisaCutiTahunan' => $saldoTahunan->sisa ?? $pegawai?->sisa_cuti_tahunan ?? 12,
        ]);
    }

    public function cancel(Request $request, CutiPengajuan $cutiPengajuan): RedirectResponse
    {
        $pegawai = $request->user()->pegawai;

        if (! $pegawai || $cutiPengajuan->pegawai_id !== $pegawai->id) {
            abort(403);
        }

        if ($cutiPengajuan->status !== 'diajukan') {
            return back()->with('sync_error', 'Pengajuan ini sudah diproses dan tidak bisa dibatalkan.');
        }

        DB::transaction(function () use ($cutiPengajuan, $request) {
            $cutiPengajuan->tahap()->where('status', 'menunggu')->update(['status' => 'dilewati']);

            $cutiPengajuan->update(['status' => 'dibatalkan']);

            CutiPengajuanLog::create([
                'cuti_pengajuan_id' => $cutiPengajuan->id,
                'status_sebelum' => 'diajukan',
                'status_sesudah' => 'dibatalkan',
                'oleh' => $request->user()->id,
                'catatan' => 'Dibatalkan oleh pemohon.',
            ]);
        });

        return redirect()->route('cuti-baru.riwayat')
            ->with('status', "Pengajuan {$cutiPengajuan->nomor_pengajuan} berhasil dibatalkan.");
    }

    public function show(Request $request, CutiPengajuan $cutiPengajuan): View
    {
        $user = $request->user();
        $pegawai = $user->pegawai;

        $bolehLihat = $user->isAdmin()
            || ($pegawai && $cutiPengajuan->pegawai_id === $pegawai->id)
            || $cutiPengajuan->tahap->contains('penyetuju_id', $user->id);

        abort_unless($bolehLihat, 403);

        $cutiPengajuan->load(['pegawai.opd', 'atasanLangsungPegawai', 'jenisCutiAturan', 'tahap.penyetuju', 'lampiran']);

        return view('cuti-baru.show', ['pengajuan' => $cutiPengajuan]);
    }

    /**
     * Inbox "Persetujuan Saya" -- hanya jenjang 1 & 2. Jenjang 'admin'
     * TIDAK pernah muncul di sini, itu wewenang modul terpisah
     * PersetujuanAkhirCutiController (lihat Fase 4.5).
     */
    public function persetujuan(Request $request): View
    {
        $user = $request->user();

        $kandidat = CutiPengajuanTahap::query()
            ->with(['cutiPengajuan.pegawai.opd', 'cutiPengajuan.jenisCutiAturan'])
            ->whereIn('jenjang', ['atasan_langsung', 'kepala_unit_kerja'])
            ->where('status', 'menunggu')
            ->whereRaw('urutan = (
                select min(urutan) from cuti_pengajuan_tahap as t2
                where t2.cuti_pengajuan_id = cuti_pengajuan_tahap.cuti_pengajuan_id
                and t2.status = "menunggu"
            )')
            ->get()
            ->filter(fn (CutiPengajuanTahap $tahap) => Gate::forUser($user)->allows('approve', $tahap))
            ->values();

        $page = max(1, (int) $request->query('page', 1));

        $tahapList = new LengthAwarePaginator(
            $kandidat->forPage($page, static::PER_PAGE)->values(),
            $kandidat->count(),
            static::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('cuti-baru.persetujuan', ['tahapList' => $tahapList]);
    }

    public function setujui(Request $request, CutiPengajuanTahap $cutiPengajuanTahap): RedirectResponse
    {
        Gate::authorize('approve', $cutiPengajuanTahap);

        if ($cutiPengajuanTahap->jenjang === 'admin') {
            abort(404);
        }

        $this->keputusanService->setujui($cutiPengajuanTahap, $request->user(), $request->input('catatan'));

        return redirect()->route('cuti-baru.persetujuan')
            ->with('status', "Pengajuan {$cutiPengajuanTahap->cutiPengajuan->nomor_pengajuan} berhasil disetujui.");
    }

    public function tolak(Request $request, CutiPengajuanTahap $cutiPengajuanTahap): RedirectResponse
    {
        Gate::authorize('approve', $cutiPengajuanTahap);

        if ($cutiPengajuanTahap->jenjang === 'admin') {
            abort(404);
        }

        $validated = $request->validate(['catatan' => ['required', 'string', 'max:1000']]);

        $this->keputusanService->tolak($cutiPengajuanTahap, $request->user(), $validated['catatan']);

        return redirect()->route('cuti-baru.persetujuan')
            ->with('status', "Pengajuan {$cutiPengajuanTahap->cutiPengajuan->nomor_pengajuan} ditolak.");
    }
}
