<?php

return [
    /**
     * Registry menu sidebar terpusat -- dipakai oleh layouts.sidebar
     * (render + filter visibilitas) dan Master > Hak Akses Menu (render
     * matrix pengaturan). Ubah/tambah menu cukup di sini.
     *
     * Setiap elemen top-level: item tunggal ({route,label,icon}) atau
     * grup ({group,items:[...]}). Item di dalam grup bisa diberi
     * 'admin' => true supaya SELALU eksklusif Admin (tidak pernah bisa
     * diberikan ke role lain lewat Hak Akses Menu, karena rute-nya juga
     * dilindungi middleware 'admin'). Item tanpa 'admin' => true adalah
     * "menu yang bisa diatur" -- defaultnya TERTUTUP untuk role opd/user
     * kecuali diizinkan lewat tabel role_menu_permissions.
     */
    'sections' => [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        [
            'group' => 'Srimanganti',
            'items' => [
                ['route' => 'settings.koneksi', 'label' => 'Setting Koneksi', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z', 'admin' => true],
                ['route' => 'sync-data', 'label' => 'Sync Data', 'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', 'admin' => true],
                ['route' => 'data.opd', 'label' => 'OPD Simabs', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2M5 21h2m0 0h10M5 21v-4a1 1 0 011-1h1m8 5v-4a1 1 0 00-1-1h-1m-4-3h.01M9 9h.01M9 13h.01M13 9h.01'],
                ['route' => 'data.pegawai', 'label' => 'Pegawai Simabs', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                ['route' => 'summary-cuti', 'label' => 'Summary Cuti', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['route' => 'rekapitulasi', 'label' => 'Rekapitulasi Cuti', 'icon' => 'M9 17v-2a4 4 0 014-4h4M9 17H7a2 2 0 01-2-2V5a2 2 0 012-2h10a2 2 0 012 2v4M9 17l3 3m0 0l3-3m-3 3V9'],
                ['route' => 'cari-cuti', 'label' => 'Cari Data Cuti', 'icon' => 'M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z'],
            ],
        ],
        [
            'group' => 'Cuti Baru',
            'items' => [
                ['route' => 'cuti-baru.ajukan', 'label' => 'Ajukan Cuti', 'icon' => 'M12 4v16m8-8H4'],
                ['route' => 'cuti-baru.riwayat', 'label' => 'Riwayat Cuti Saya', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['route' => 'cuti-baru.persetujuan', 'label' => 'Persetujuan Saya', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['route' => 'cuti-baru.persetujuan-akhir', 'label' => 'Persetujuan Akhir Besar/CLTN', 'icon' => 'M9 12l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0zM12 8v1m0 6v1', 'admin' => true],
            ],
        ],
        [
            'group' => 'Setting Master',
            'items' => [
                ['route' => 'master.unit-kerja', 'label' => 'Unit Kerja', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2M5 21h2m0 0h10M5 21v-4a1 1 0 011-1h1m8 5v-4a1 1 0 00-1-1h-1m-4-3h.01M9 9h.01M9 13h.01M13 9h.01', 'admin' => true],
                ['route' => 'master.pengguna', 'label' => 'Pengguna', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'admin' => true],
                ['route' => 'master.pegawai', 'label' => 'Master Pegawai', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'admin' => true],
                ['route' => 'master.jenis-cuti', 'label' => 'Aturan Cuti', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'admin' => true],
                ['route' => 'master.log-aktivitas', 'label' => 'Log Aktivitas', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'admin' => true],
                ['route' => 'master.pengumuman', 'label' => 'Broadcast Pengumuman', 'icon' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z', 'admin' => true],
                ['route' => 'master.akses-menu', 'label' => 'Hak Akses Menu', 'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'admin' => true],
            ],
        ],
    ],
];
