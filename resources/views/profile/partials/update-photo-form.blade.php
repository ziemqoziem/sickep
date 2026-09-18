<section x-data="{ preview: null }">
    <header>
        <h2 class="text-base font-semibold text-slate-900">
            {{ __('Foto Profil') }}
        </h2>
        <p class="mt-1 text-sm text-slate-500">
            {{ __('Unggah foto untuk mempersonalisasi akun Anda. Format JPG, PNG, atau WEBP, maksimal 2MB.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data" class="mt-6 flex items-center gap-6">
        @csrf

        <div class="relative shrink-0">
            <img :src="preview ?? '{{ $user->photoUrl() }}'"
                 src="{{ $user->photoUrl() }}"
                 alt="{{ $user->name }}"
                 class="h-20 w-20 rounded-full object-cover ring-4 ring-sky-50 border border-sky-100">
        </div>

        <div class="flex-1">
            <label for="photo"
                   class="inline-flex items-center gap-2 rounded-lg border border-sky-200 bg-sky-50 px-4 py-2 text-sm font-medium text-sky-700 cursor-pointer hover:bg-sky-100 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                {{ __('Pilih Foto') }}
            </label>
            <input id="photo" name="photo" type="file" accept="image/png, image/jpeg, image/webp" class="hidden"
                   @change="
                        const file = $event.target.files[0];
                        if (file) {
                            preview = URL.createObjectURL(file);
                            $nextTick(() => $refs.uploadBtn.disabled = false);
                        }
                   ">

            <button x-ref="uploadBtn" type="submit" disabled
                    class="ms-2 inline-flex items-center rounded-lg bg-gradient-to-r from-sky-600 to-sky-700 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-sky-600/20 hover:from-sky-700 hover:to-sky-800 disabled:opacity-40 disabled:cursor-not-allowed transition">
                {{ __('Unggah') }}
            </button>

            <x-input-error :messages="$errors->get('photo')" class="mt-2" />

            @if (session('status') === 'photo-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="mt-2 text-sm text-emerald-600"
                >{{ __('Foto profil berhasil diperbarui.') }}</p>
            @endif
        </div>
    </form>
</section>
