<section>
    <header>
        <h2 class="text-base font-semibold text-slate-900">
            {{ __('Informasi Profil') }}
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            {{ __('Perbarui nama, email, dan nomor HP akun Anda.') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <div>
            <label for="username" class="block text-sm font-medium text-slate-700">{{ __('Username (login)') }}</label>
            <input id="username" type="text" value="{{ $user->username }}" disabled
                   class="mt-1.5 block w-full rounded-lg border-slate-200 bg-slate-50 shadow-sm text-sm text-slate-500 font-mono">
            <p class="mt-1 text-xs text-slate-400">{{ __('Username tidak bisa diubah sendiri. Hubungi Admin bila perlu diganti.') }}</p>
        </div>

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700">{{ __('Nama') }}</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name"
                   class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-sky-500 focus:ring-sky-500">
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">{{ __('Email (opsional)') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" autocomplete="email"
                   class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-sky-500 focus:ring-sky-500">
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
            <p class="mt-1 text-xs text-slate-400">{{ __('Dipakai untuk profil dan notifikasi, bukan untuk login.') }}</p>

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-slate-600">
                        {{ __('Alamat email Anda belum terverifikasi.') }}

                        <button form="send-verification" class="underline font-medium text-sky-600 hover:text-sky-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">
                            {{ __('Klik di sini untuk mengirim ulang email verifikasi.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-emerald-600">
                            {{ __('Tautan verifikasi baru telah dikirim ke alamat email Anda.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <label for="no_hp" class="block text-sm font-medium text-slate-700">{{ __('No. HP (opsional)') }}</label>
            <input id="no_hp" name="no_hp" type="text" value="{{ old('no_hp', $user->no_hp) }}" autocomplete="tel"
                   class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-sky-500 focus:ring-sky-500">
            <x-input-error class="mt-2" :messages="$errors->get('no_hp')" />
            <p class="mt-1 text-xs text-slate-400">{{ __('Dipakai untuk notifikasi.') }}</p>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit"
                    class="inline-flex items-center rounded-lg bg-gradient-to-r from-sky-600 to-sky-700 px-5 py-2 text-sm font-semibold text-white shadow-sm shadow-sky-600/20 hover:from-sky-700 hover:to-sky-800 transition">
                {{ __('Simpan') }}
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-emerald-600"
                >{{ __('Tersimpan.') }}</p>
            @endif
        </div>
    </form>
</section>
