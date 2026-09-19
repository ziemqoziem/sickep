@php
    $tampilkanPengumuman = Auth::check() && session('just_logged_in');
    $daftarPengumuman = $tampilkanPengumuman
        ? \App\Models\Pengumuman::aktifHariIni()->orderByDesc('tanggal_mulai')->get(['judul', 'isi'])
        : collect();
@endphp

@if ($daftarPengumuman->isNotEmpty())
    <div x-data="{
            open: true,
            index: 0,
            items: @js($daftarPengumuman),
        }"
         x-show="open" x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center p-4" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-indigo-600 to-violet-700 px-6 py-5">
                <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/10"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/15 shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-indigo-100 uppercase tracking-wider">Pengumuman</p>
                        <p class="text-white font-bold truncate" x-text="items[index].judul"></p>
                    </div>
                </div>
            </div>

            <div class="p-6 max-h-[45vh] overflow-y-auto">
                <p class="text-sm text-slate-600 whitespace-pre-line" x-text="items[index].isi"></p>
            </div>

            <div class="flex items-center justify-between gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                <p class="text-xs text-slate-400" x-show="items.length > 1">
                    <span x-text="index + 1"></span> / <span x-text="items.length"></span>
                </p>
                <div class="flex gap-2 ml-auto">
                    <button type="button" x-show="items.length > 1 && index > 0" @click="index--"
                            class="px-3 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                        &larr; Sebelumnya
                    </button>
                    <button type="button" x-show="items.length > 1 && index < items.length - 1" @click="index++"
                            class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-violet-700 hover:from-indigo-700 hover:to-violet-800 transition">
                        Berikutnya &rarr;
                    </button>
                    <button type="button" x-show="index === items.length - 1" @click="open = false"
                            class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-violet-700 hover:from-indigo-700 hover:to-violet-800 transition">
                        Mengerti
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
