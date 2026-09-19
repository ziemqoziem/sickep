<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Profil Saya') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Profile summary banner -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-sky-600 to-sky-700 px-6 py-8 sm:px-10 shadow-md shadow-sky-600/20">
                <div class="absolute -right-8 -top-8 w-40 h-40 rounded-full bg-white/10"></div>
                <div class="absolute -left-6 -bottom-10 w-32 h-32 rounded-full bg-white/10"></div>

                <div class="relative flex items-center gap-5">
                    <img src="{{ $user->photoUrl() }}" alt="{{ $user->name }}"
                         class="h-16 w-16 rounded-full object-cover ring-4 ring-white/30 border border-white/40">
                    <div>
                        <p class="text-lg font-bold text-white">{{ $user->name }}</p>
                        <p class="text-sm text-sky-100 font-mono">{{ $user->username }}</p>
                        <span class="mt-1 inline-flex items-center rounded-full bg-white/15 text-white text-xs font-medium px-2.5 py-0.5 capitalize">
                            {{ $user->role }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-6 sm:p-8 bg-white shadow-sm rounded-2xl border border-sky-100">
                @include('profile.partials.update-photo-form')
            </div>

            <div class="p-6 sm:p-8 bg-white shadow-sm rounded-2xl border border-sky-100">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-6 sm:p-8 bg-white shadow-sm rounded-2xl border border-sky-100">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
