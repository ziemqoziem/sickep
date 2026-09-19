<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Setting Master') }} &mdash; {{ __('Log Aktivitas') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Hero --}}
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-700 via-slate-700 to-indigo-800 p-6 sm:p-8 shadow-lg shadow-slate-700/20">
                <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-white/10"></div>
                <div class="absolute -left-8 -bottom-10 w-32 h-32 rounded-full bg-white/10"></div>
                <div class="relative flex items-center gap-4">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/15 shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-lg sm:text-xl font-bold text-white">Log Aktivitas</p>
                        <p class="text-sm text-slate-200 mt-0.5">Rekam jejak penggunaan aplikasi &mdash; siapa, dari IP mana, aktivitas apa, dan kapan</p>
                    </div>
                </div>
            </div>

            {{-- KPI tiles --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-500 to-sky-600 p-5 shadow-lg shadow-sky-600/20">
                    <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-white/15 shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-sky-100 truncate">Aktivitas Hari Ini</p>
                            <p class="text-xl font-bold text-white">{{ number_format($summary['hari_ini']) }}</p>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-600 p-5 shadow-lg shadow-indigo-600/20">
                    <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-white/15 shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-indigo-100 truncate">7 Hari Terakhir</p>
                            <p class="text-xl font-bold text-white">{{ number_format($summary['tujuh_hari']) }}</p>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 p-5 shadow-lg shadow-emerald-600/20">
                    <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-white/15 shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-emerald-100 truncate">Pengguna Aktif Hari Ini</p>
                            <p class="text-xl font-bold text-white">{{ number_format($summary['pengguna_aktif_hari_ini']) }}</p>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-500 to-rose-600 p-5 shadow-lg shadow-rose-600/20">
                    <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-white/15 shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-rose-100 truncate">Login Gagal Hari Ini</p>
                            <p class="text-xl font-bold text-white">{{ number_format($summary['login_gagal_hari_ini']) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter --}}
            <div class="bg-white border border-sky-100 rounded-2xl shadow-sm p-4 sm:p-5">
                <form method="GET" action="{{ route('master.log-aktivitas') }}" class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Cari</label>
                        <input type="text" name="q" value="{{ $q }}" placeholder="Aktivitas, nama, IP, atau URL..."
                               class="block w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Pengguna</label>
                        <select name="user_id" onchange="this.form.submit()"
                                class="rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                            <option value="">Semua Pengguna</option>
                            <option value="0" {{ $userId === '0' ? 'selected' : '' }}>Tamu (belum login)</option>
                            @foreach ($userList as $u)
                                <option value="{{ $u->id }}" {{ (string) $userId === (string) $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Method</label>
                        <select name="method" onchange="this.form.submit()"
                                class="rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                            <option value="">Semua</option>
                            @foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $m)
                                <option value="{{ $m }}" {{ $method === $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Dari</label>
                        <input type="date" name="dari" value="{{ $dari }}"
                               class="rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Sampai</label>
                        <input type="date" name="sampai" value="{{ $sampai }}"
                               class="rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Per halaman</label>
                        <select name="per_page" onchange="this.form.submit()"
                                class="rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500 text-sm">
                            @foreach ($perPageOptions as $opt)
                                <option value="{{ $opt }}" {{ $perPage === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-sky-600 rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-sky-700 transition">
                            Filter
                        </button>
                        @if ($q !== '' || $userId !== '' || $method !== '' || $dari !== '' || $sampai !== '')
                            <a href="{{ route('master.log-aktivitas') }}"
                               class="inline-flex items-center px-3 py-2 rounded-lg text-xs font-medium text-slate-500 hover:bg-slate-100 transition">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="bg-white border border-sky-100 rounded-2xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-sky-100">
                    <thead class="bg-sky-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Waktu</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Pengguna</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">IP Address</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Aktivitas</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-sky-700 uppercase tracking-wider">Method</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($logs as $log)
                            @php $gagal = $log->aktivitas === 'Percobaan login gagal'; @endphp
                            <tr class="{{ $gagal ? 'bg-rose-50/40' : '' }} hover:bg-slate-50 transition">
                                <td class="px-6 py-3 text-sm text-slate-600 whitespace-nowrap">
                                    {{ $log->created_at->translatedFormat('d M Y') }}
                                    <span class="text-slate-400">{{ $log->created_at->format('H:i:s') }}</span>
                                </td>
                                <td class="px-6 py-3 text-sm">
                                    @if ($log->user)
                                        <p class="font-medium text-slate-800">{{ $log->user->name }}</p>
                                        <p class="text-xs text-slate-400 font-mono">{{ $log->user->username }}</p>
                                    @else
                                        <p class="text-slate-500">{{ $log->nama_pengguna ?: 'Tamu' }}</p>
                                        <p class="text-xs text-slate-400">Belum login</p>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-600 font-mono">{{ $log->ip_address ?: '—' }}</td>
                                <td class="px-6 py-3 text-sm">
                                    <p @class([
                                        'font-medium',
                                        'text-rose-700' => $gagal,
                                        'text-slate-700' => ! $gagal,
                                    ])>{{ $log->aktivitas }}</p>
                                    <p class="text-xs text-slate-400 font-mono truncate max-w-xs" title="{{ $log->url }}">{{ $log->route_name }}</p>
                                </td>
                                <td class="px-6 py-3 text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $log->methodWarna() }}">
                                        {{ $log->method }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-400">
                                    Belum ada aktivitas tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $logs->links() }}
        </div>
    </div>
</x-app-layout>
