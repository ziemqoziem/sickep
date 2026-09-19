<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SICKEP') }} &mdash; Masuk</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/logo-klaten.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-700">
        <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-sky-50 via-white to-sky-100 px-4 py-10">

            <div class="w-full max-w-md">
                <div class="relative overflow-hidden bg-white rounded-2xl shadow-xl border border-sky-100">
                    <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-sky-50"></div>
                    <div class="absolute -left-10 -bottom-10 w-32 h-32 rounded-full bg-sky-50/70"></div>

                    <div class="relative px-8 pt-10 pb-8">
                        <!-- Brand -->
                        <div class="flex flex-col items-center text-center mb-8">
                            <img src="{{ asset('images/logo-klaten.png') }}" alt="Logo Kabupaten Klaten" class="h-20 w-auto object-contain">
                            <p class="mt-4 text-2xl font-extrabold text-sky-900 tracking-wide">SICKEP</p>
                            <p class="text-sm text-slate-500">Sistem Informasi Cuti Kepegawaian</p>
                        </div>

                        <!-- Session Status -->
                        @if (session('status'))
                            <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" class="space-y-5">
                            @csrf

                            <!-- Username -->
                            <div>
                                <label for="username" class="block text-sm font-medium text-slate-700">Username</label>
                                <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus autocomplete="username"
                                       class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-sky-500 focus:ring-sky-500">
                                <x-input-error :messages="$errors->get('username')" class="mt-2" />
                            </div>

                            <!-- Password -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                                <input id="password" type="password" name="password" required autocomplete="current-password"
                                       class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-sky-500 focus:ring-sky-500">
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <!-- Remember Me -->
                            <div class="flex items-center justify-between">
                                <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                                    <input id="remember_me" type="checkbox" name="remember"
                                           class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                                    <span class="text-sm text-slate-600">Ingat saya</span>
                                </label>

                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-sky-600 hover:text-sky-700">
                                        Lupa password?
                                    </a>
                                @endif
                            </div>

                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-sky-600 to-sky-700 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-sky-600/20 hover:from-sky-700 hover:to-sky-800 transition">
                                Masuk
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

                <p class="mt-6 text-center text-xs text-slate-400">
                    &copy; {{ date('Y') }} Pemerintah Kabupaten Klaten &mdash; SICKEP
                </p>
            </div>
        </div>
    </body>
</html>
