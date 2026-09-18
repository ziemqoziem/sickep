<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ $title }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border border-slate-100 rounded-2xl p-10 text-center">
                <div class="mx-auto flex items-center justify-center w-12 h-12 rounded-xl bg-sky-50 text-sky-600 mb-4">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4m6 0a10 10 0 11-20 0 10 10 0 0120 0z" />
                    </svg>
                </div>
                <p class="font-medium text-slate-700">Halaman {{ $title }} belum tersedia</p>
                <p class="text-sm text-slate-400 mt-1">Fitur ini sedang dalam pengembangan.</p>
            </div>
        </div>
    </div>
</x-app-layout>
