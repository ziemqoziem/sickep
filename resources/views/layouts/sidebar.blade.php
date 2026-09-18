@php
$iconDashboard = 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6';
$iconOpd = 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2M5 21h2m0 0h10M5 21v-4a1 1 0 011-1h1m8 5v-4a1 1 0 00-1-1h-1m-4-3h.01M9 9h.01M9 13h.01M13 9h.01';
$iconPegawai = 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z';
$iconCariCuti = 'M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z';
$iconSync = 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15';
$iconKoneksi = 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z';
$iconSummary = 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6';
$iconMenuBaru = 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4';
$iconUnitKerja = 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2M5 21h2m0 0h10M5 21v-4a1 1 0 011-1h1m8 5v-4a1 1 0 00-1-1h-1m-4-3h.01M9 9h.01M9 13h.01M13 9h.01';
$iconPengguna = 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z';
$iconMasterPegawai = 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z';
$iconRekapitulasi = 'M9 17v-2a4 4 0 014-4h4M9 17H7a2 2 0 01-2-2V5a2 2 0 012-2h10a2 2 0 012 2v4M9 17l3 3m0 0l3-3m-3 3V9';

// Setiap elemen: item tunggal ({route,label,icon}) atau grup ({label, items:[...]}).
// Item di dalam grup bisa diberi 'admin' => true supaya hanya tampil untuk admin,
// meski grupnya sendiri tetap tampil untuk semua user.
$navSections = [
    ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => $iconDashboard],
    [
        'group' => 'Srimanganti',
        'items' => [
            ['route' => 'settings.koneksi', 'label' => 'Setting Koneksi', 'icon' => $iconKoneksi, 'admin' => true],
            ['route' => 'sync-data', 'label' => 'Sync Data', 'icon' => $iconSync, 'admin' => true],
            ['route' => 'data.opd', 'label' => 'OPD Simabs', 'icon' => $iconOpd],
            ['route' => 'data.pegawai', 'label' => 'Pegawai Simabs', 'icon' => $iconPegawai],
            ['route' => 'summary-cuti', 'label' => 'Summary Cuti', 'icon' => $iconSummary],
            ['route' => 'rekapitulasi', 'label' => 'Rekapitulasi Cuti', 'icon' => $iconRekapitulasi],
            ['route' => 'cari-cuti', 'label' => 'Cari Data Cuti', 'icon' => $iconCariCuti],
        ],
    ],
    [
        'group' => 'Cuti Baru',
        'items' => [
            ['route' => 'cuti-baru.menu1', 'label' => 'Menu 1', 'icon' => $iconMenuBaru],
            ['route' => 'cuti-baru.menu2', 'label' => 'Menu 2', 'icon' => $iconMenuBaru],
            ['route' => 'cuti-baru.menu3', 'label' => 'Menu 3', 'icon' => $iconMenuBaru],
            ['route' => 'cuti-baru.menu4', 'label' => 'Menu 4', 'icon' => $iconMenuBaru],
        ],
    ],
    [
        'group' => 'Setting Master',
        'items' => [
            ['route' => 'master.unit-kerja', 'label' => 'Unit Kerja', 'icon' => $iconUnitKerja, 'admin' => true],
            ['route' => 'master.pengguna', 'label' => 'Pengguna', 'icon' => $iconPengguna, 'admin' => true],
            ['route' => 'master.pegawai', 'label' => 'Master Pegawai', 'icon' => $iconMasterPegawai, 'admin' => true],
        ],
    ],
];
@endphp

<!-- Desktop sidebar -->
<aside class="hidden lg:flex lg:flex-col lg:w-64 lg:fixed lg:inset-y-0 bg-white border-r border-sky-100 print:hidden">
    <div class="flex items-center px-5 h-16 border-b border-sky-100">
        <img src="{{ asset('images/brandapps.png') }}" alt="SICKEP - Sistem Informasi Cuti Kepegawaian" class="h-11 w-auto object-contain">
    </div>

    <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
        @include('layouts.partials.nav-items')
    </nav>

    <div class="border-t border-sky-100 p-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 shrink-0 rounded-full bg-sky-100 flex items-center justify-center text-sky-700 font-semibold text-sm">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="text-sm font-medium text-slate-800 truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-slate-400 truncate">{{ Auth::user()->email }}</p>
            </div>
        </div>
        <div class="mt-3 flex items-center gap-2">
            <a href="{{ route('profile.edit') }}"
               class="flex-1 text-center text-xs font-medium text-sky-700 border border-sky-200 rounded-md py-1.5 hover:bg-sky-50 transition">
                Profil
            </a>
            <form method="POST" action="{{ route('logout') }}" class="flex-1">
                @csrf
                <button type="submit"
                        class="w-full text-center text-xs font-medium text-red-600 border border-red-200 rounded-md py-1.5 hover:bg-red-50 transition">
                    Keluar
                </button>
            </form>
        </div>
    </div>
</aside>

<!-- Mobile sidebar (slide-over) -->
<div x-show="sidebarOpen" x-cloak class="lg:hidden print:hidden fixed inset-0 z-40">
    <div class="fixed inset-0 bg-slate-900/50" @click="sidebarOpen = false"></div>

    <div class="relative flex flex-col w-64 h-full bg-white shadow-xl"
         x-show="sidebarOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full">
        <div class="flex items-center justify-between px-5 h-16 border-b border-sky-100">
            <img src="{{ asset('images/brandapps.png') }}" alt="SICKEP - Sistem Informasi Cuti Kepegawaian" class="h-11 w-auto object-contain">
            <button @click="sidebarOpen = false" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
            @include('layouts.partials.nav-items')
        </nav>

        <div class="border-t border-sky-100 p-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 shrink-0 rounded-full bg-sky-100 flex items-center justify-center text-sky-700 font-semibold text-sm">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-medium text-slate-800 truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ Auth::user()->email }}</p>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <a href="{{ route('profile.edit') }}"
                   class="flex-1 text-center text-xs font-medium text-sky-700 border border-sky-200 rounded-md py-1.5 hover:bg-sky-50 transition">
                    Profil
                </a>
                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button type="submit"
                            class="w-full text-center text-xs font-medium text-red-600 border border-red-200 rounded-md py-1.5 hover:bg-red-50 transition">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
