<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Setting Master') }} &mdash; {{ __('Hak Akses Menu') }}
        </h2>
    </x-slot>

    @php
        $sectionsBisaDiatur = collect($sections)->filter(fn ($s) => isset($s['group']));
    @endphp

    <div class="py-10" x-data="{
        checked: {
            opd: @js($granted['opd']),
            user: @js($granted['user']),
        },
        toggleAll(role, routes, value) {
            if (value) {
                this.checked[role] = Array.from(new Set([...this.checked[role], ...routes]));
            } else {
                this.checked[role] = this.checked[role].filter(r => ! routes.includes(r));
            }
        },
    }">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-700 via-slate-800 to-slate-900 p-6 sm:p-8 shadow-lg shadow-slate-800/20">
                <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-white/10"></div>
                <div class="absolute -left-8 -bottom-10 w-32 h-32 rounded-full bg-white/10"></div>
                <div class="relative flex items-center gap-4">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/15 shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-lg sm:text-xl font-bold text-white">Hak Akses Menu</p>
                        <p class="text-sm text-slate-300 mt-0.5">
                            Menu sidebar hanya terbuka untuk role OPD/User kalau dicentang di sini. Admin selalu bisa mengakses semua menu.
                        </p>
                    </div>
                </div>
            </div>

            @if (session('status'))
                <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('master.akses-menu.update') }}" class="space-y-6">
                @csrf

                @foreach ($sectionsBisaDiatur as $section)
                    @php
                        $itemsBisaDiatur = collect($section['items'])->reject(fn ($item) => $item['admin'] ?? false);
                        $itemsAdmin = collect($section['items'])->filter(fn ($item) => $item['admin'] ?? false);
                        $routesGroup = $itemsBisaDiatur->pluck('route')->values();
                    @endphp

                    <div class="bg-white border border-sky-100 rounded-2xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60">
                            <h3 class="font-semibold text-slate-800">{{ $section['group'] }}</h3>
                        </div>

                        @if ($itemsBisaDiatur->isNotEmpty())
                            <table class="min-w-full divide-y divide-slate-100">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Menu</th>
                                        <th class="px-4 py-2.5 text-center text-xs font-semibold text-teal-600 uppercase tracking-wider w-28">
                                            <label class="inline-flex flex-col items-center gap-1 cursor-pointer">
                                                User
                                                <input type="checkbox"
                                                       @change="toggleAll('user', @js($routesGroup), $event.target.checked)"
                                                       :checked="@js($routesGroup)->every(r => checked.user.includes(r))"
                                                       class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                                            </label>
                                        </th>
                                        <th class="px-4 py-2.5 text-center text-xs font-semibold text-violet-600 uppercase tracking-wider w-28">
                                            <label class="inline-flex flex-col items-center gap-1 cursor-pointer">
                                                OPD
                                                <input type="checkbox"
                                                       @change="toggleAll('opd', @js($routesGroup), $event.target.checked)"
                                                       :checked="@js($routesGroup)->every(r => checked.opd.includes(r))"
                                                       class="rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                                            </label>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($itemsBisaDiatur as $item)
                                        <tr>
                                            <td class="px-6 py-3 text-sm">
                                                <div class="flex items-center gap-2.5">
                                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                                                    </svg>
                                                    <span class="text-slate-700">{{ $item['label'] }}</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <input type="checkbox" name="user[]" value="{{ $item['route'] }}"
                                                       x-model="checked.user"
                                                       class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <input type="checkbox" name="opd[]" value="{{ $item['route'] }}"
                                                       x-model="checked.opd"
                                                       class="rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                        @if ($itemsAdmin->isNotEmpty())
                            <div class="px-6 py-3 bg-slate-50/40 space-y-1.5">
                                @foreach ($itemsAdmin as $item)
                                    <div class="flex items-center gap-2.5 text-sm text-slate-400">
                                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        <span>{{ $item['label'] }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-200 text-slate-500 ml-auto">
                                            Khusus Admin
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach

                <div class="flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-slate-700 to-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-slate-800/20 hover:from-slate-800 hover:to-black transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
