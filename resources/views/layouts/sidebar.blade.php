@php
$navSections = config('menu.sections');

$allowedMenuRoutes = (Auth::check() && ! Auth::user()->isAdmin())
    ? \App\Models\RoleMenuPermission::where('role', Auth::user()->role)->pluck('menu_route')->all()
    : [];
@endphp

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
                <p class="text-xs text-slate-400 truncate">{{ Auth::user()->username }}</p>
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
                    <p class="text-xs text-slate-400 truncate">{{ Auth::user()->username }}</p>
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
