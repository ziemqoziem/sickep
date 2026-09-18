<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SICKEP') }} &mdash; Sistem Informasi Cuti Kepegawaian</title>
        <meta name="description" content="SICKEP — Sistem Informasi Cuti Kepegawaian Pemerintah Kabupaten Klaten.">

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/logo-klaten.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-700">
        <div class="min-h-screen bg-gradient-to-b from-sky-50 via-white to-white">

            <!-- Top bar -->
            <header class="border-b border-sky-100 bg-white/80 backdrop-blur">
                <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/brandapps.png') }}" alt="SICKEP - Sistem Informasi Cuti Kepegawaian" class="h-11 w-auto object-contain">
                    </div>

                    @if (Route::has('login'))
                        <nav class="flex items-center gap-3">
                            @auth
                                <a href="{{ url('/dashboard') }}"
                                   class="inline-flex items-center rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-sky-700 transition">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}"
                                   class="inline-flex items-center rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-sky-700 transition">
                                    Masuk
                                </a>
                            @endauth
                        </nav>
                    @endif
                </div>
            </header>

            <!-- Hero -->
            <section class="max-w-6xl mx-auto px-6 pt-16 pb-20">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-sky-100 text-sky-700 text-xs font-semibold px-3 py-1">
                            Pemerintah Kabupaten Klaten
                        </span>
                        <h1 class="mt-5 text-4xl sm:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                            Sistem Informasi
                            <span class="text-sky-600">Cuti Kepegawaian</span>
                        </h1>
                        <p class="mt-5 text-lg text-slate-600 leading-relaxed">
                            SICKEP membantu memantau, mengelola, dan menganalisis pengajuan cuti pegawai
                            di lingkungan Pemerintah Kabupaten Klaten secara terpusat, cepat, dan akurat.
                        </p>

                        <div class="mt-8 flex flex-wrap gap-3">
                            @auth
                                <a href="{{ url('/dashboard') }}"
                                   class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-6 py-3 text-sm font-semibold text-white shadow-md shadow-sky-600/20 hover:bg-sky-700 transition">
                                    Buka Dashboard
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </a>
                            @else
                                <a href="{{ route('login') }}"
                                   class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-6 py-3 text-sm font-semibold text-white shadow-md shadow-sky-600/20 hover:bg-sky-700 transition">
                                    Masuk ke Sistem
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </a>
                            @endauth
                        </div>
                    </div>

                    <div class="relative">
                        <div class="absolute -inset-4 bg-sky-100 rounded-3xl blur-2xl opacity-60"></div>
                        <div class="relative bg-white border border-sky-100 rounded-2xl shadow-xl p-6">
                            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                                <p class="font-semibold text-slate-800">Ringkasan Cuti</p>
                                <span class="text-xs text-slate-400">Tahun berjalan</span>
                            </div>
                            <div class="grid grid-cols-2 gap-4 mt-5">
                                <div class="rounded-xl bg-sky-50 p-4">
                                    <p class="text-2xl font-bold text-sky-700">12.448</p>
                                    <p class="text-xs text-slate-500 mt-1">Total Pegawai</p>
                                </div>
                                <div class="rounded-xl bg-emerald-50 p-4">
                                    <p class="text-2xl font-bold text-emerald-600">109</p>
                                    <p class="text-xs text-slate-500 mt-1">OPD Terdaftar</p>
                                </div>
                                <div class="rounded-xl bg-amber-50 p-4">
                                    <p class="text-2xl font-bold text-amber-600">0</p>
                                    <p class="text-xs text-slate-500 mt-1">Pengajuan Pending</p>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-4">
                                    <p class="text-2xl font-bold text-slate-700">Real-time</p>
                                    <p class="text-xs text-slate-500 mt-1">Status Sinkronisasi</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Feature highlights -->
            <section class="max-w-6xl mx-auto px-6 pb-24">
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="rounded-2xl border border-sky-100 bg-white p-6 hover:shadow-md transition">
                        <div class="w-11 h-11 rounded-lg bg-sky-100 flex items-center justify-center text-sky-600">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 font-semibold text-slate-900">Dashboard KPI</h3>
                        <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                            Ringkasan cuti pegawai per OPD, jenis cuti, dan status persetujuan dalam satu tampilan.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-sky-100 bg-white p-6 hover:shadow-md transition">
                        <div class="w-11 h-11 rounded-lg bg-sky-100 flex items-center justify-center text-sky-600">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 font-semibold text-slate-900">Pencarian &amp; Filter Cuti</h3>
                        <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                            Telusuri riwayat cuti berdasarkan nama, NIP, unit kerja, jenis cuti, dan rentang tanggal.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-sky-100 bg-white p-6 hover:shadow-md transition">
                        <div class="w-11 h-11 rounded-lg bg-sky-100 flex items-center justify-center text-sky-600">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <h3 class="mt-4 font-semibold text-slate-900">Sinkronisasi Berkala</h3>
                        <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                            Data pegawai dan riwayat cuti diperbarui secara berkala dari sumber data kepegawaian.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Footer -->
            <footer class="border-t border-sky-100">
                <div class="max-w-6xl mx-auto px-6 py-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-slate-400">
                    <p>&copy; {{ date('Y') }} Pemerintah Kabupaten Klaten &mdash; SICKEP</p>
                    <p>Sistem Informasi Cuti Kepegawaian</p>
                </div>
            </footer>
        </div>
    </body>
</html>
