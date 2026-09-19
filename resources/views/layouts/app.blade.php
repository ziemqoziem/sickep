<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/logo-klaten.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen bg-sky-50/40">
            @include('layouts.sidebar')

            <div class="lg:pl-64 print:pl-0 flex flex-col min-h-screen">
                <!-- Mobile top bar -->
                <div class="lg:hidden print:hidden sticky top-0 z-30 flex items-center gap-3 bg-white border-b border-sky-100 px-4 h-16">
                    <button @click="sidebarOpen = true" class="text-slate-500 hover:text-sky-700">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <img src="{{ asset('images/brandapps.png') }}" alt="SICKEP - Sistem Informasi Cuti Kepegawaian" class="h-9 w-auto object-contain">
                </div>

                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-white border-b border-sky-100 print:hidden">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main class="flex-1">
                    {{ $slot }}
                </main>
            </div>

            <x-pengumuman-popup />
        </div>
    </body>
</html>
