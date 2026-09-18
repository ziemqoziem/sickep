<section>
    <header>
        <h2 class="text-base font-semibold text-slate-900">
            {{ __('Ubah Password') }}
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            {{ __('Pastikan akun Anda menggunakan password yang panjang dan acak agar tetap aman.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block text-sm font-medium text-slate-700">{{ __('Password Saat Ini') }}</label>
            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
                   class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-sky-500 focus:ring-sky-500">
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password" class="block text-sm font-medium text-slate-700">{{ __('Password Baru') }}</label>
            <input id="update_password_password" name="password" type="password" autocomplete="new-password"
                   class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-sky-500 focus:ring-sky-500">
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-sm font-medium text-slate-700">{{ __('Konfirmasi Password') }}</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                   class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-sky-500 focus:ring-sky-500">
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit"
                    class="inline-flex items-center rounded-lg bg-gradient-to-r from-sky-600 to-sky-700 px-5 py-2 text-sm font-semibold text-white shadow-sm shadow-sky-600/20 hover:from-sky-700 hover:to-sky-800 transition">
                {{ __('Simpan') }}
            </button>

            @if (session('status') === 'password-updated')
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
