<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Setting Koneksi Database Sumber (SQL Server)') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('test_success'))
                <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3">
                    {{ session('test_success') }}
                </div>
            @endif

            @if (session('test_error'))
                <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
                    {{ session('test_error') }}
                </div>
            @endif

            <div class="bg-white border border-sky-100 rounded-2xl shadow-sm p-6 sm:p-8">
                <p class="text-sm text-slate-500 mb-6">
                    Atur koneksi ke database sumber (SQL Server) di host terpisah yang akan disinkronkan ke sistem ini,
                    menggunakan SQL Server Authentication. Koneksi ini memerlukan ekstensi PHP
                    <code class="text-sky-700 bg-sky-50 px-1 rounded">pdo_odbc</code> aktif serta driver
                    <span class="font-medium">ODBC Driver 11 for SQL Server</span> ter-install di server aplikasi ini.
                </p>

                <form method="POST" action="{{ route('settings.koneksi.update') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid sm:grid-cols-3 gap-5">
                        <div class="sm:col-span-2">
                            <x-input-label for="host" value="Host / IP Address" />
                            <x-text-input id="host" name="host" type="text" class="mt-1 block w-full"
                                :value="old('host', $host)" placeholder="192.168.1.10" required />
                            <x-input-error :messages="$errors->get('host')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="port" value="Port" />
                            <x-text-input id="port" name="port" type="text" class="mt-1 block w-full"
                                :value="old('port', $port)" placeholder="1433" required />
                            <x-input-error :messages="$errors->get('port')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="database" value="Nama Database (Initial Catalog)" />
                        <x-text-input id="database" name="database" type="text" class="mt-1 block w-full"
                            :value="old('database', $database)" required />
                        <x-input-error :messages="$errors->get('database')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="driver" value="Nama Driver ODBC" />
                        <x-text-input id="driver" name="driver" type="text" class="mt-1 block w-full"
                            :value="old('driver', $driver)" required />
                        <p class="mt-1 text-xs text-slate-400">
                            Default: ODBC Driver 11 for SQL Server
                        </p>
                        <x-input-error :messages="$errors->get('driver')" class="mt-2" />
                    </div>

                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <x-input-label for="username" value="Username (SQL Server Authentication)" />
                            <x-text-input id="username" name="username" type="text" class="mt-1 block w-full"
                                :value="old('username', $username)" autocomplete="off" required />
                            <x-input-error :messages="$errors->get('username')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="password" value="Password" />
                            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                                autocomplete="new-password" placeholder="Kosongkan jika tidak berubah" />
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <x-primary-button>Simpan Pengaturan</x-primary-button>
                        <button type="submit" form="test-connection-form"
                                class="inline-flex items-center px-4 py-2 bg-white border border-sky-300 rounded-md font-semibold text-xs text-sky-700 uppercase tracking-widest hover:bg-sky-50 transition">
                            Uji Koneksi
                        </button>
                    </div>
                </form>

                <form id="test-connection-form" method="POST" action="{{ route('settings.koneksi.test') }}" class="hidden">
                    @csrf
                    <input type="hidden" name="host">
                    <input type="hidden" name="port">
                    <input type="hidden" name="database">
                    <input type="hidden" name="driver">
                    <input type="hidden" name="username">
                    <input type="hidden" name="password">
                </form>
            </div>
        </div>
    </div>

    <script>
        // Sinkronkan nilai form utama ke form uji koneksi tersembunyi sebelum submit.
        document.getElementById('test-connection-form').addEventListener('submit', function () {
            const fields = ['host', 'port', 'database', 'driver', 'username', 'password'];
            fields.forEach((name) => {
                const source = document.getElementById(name);
                const target = this.querySelector(`input[name="${name}"]`);
                if (source && target) {
                    target.value = source.value;
                }
            });
        });
    </script>
</x-app-layout>
