@foreach ($navSections as $section)
    @if (isset($section['group']))
        @php
            $visibleItems = collect($section['items'])->filter(fn ($item) => ! ($item['admin'] ?? false) || Auth::user()->isAdmin());
        @endphp
        @if ($visibleItems->isNotEmpty())
            <p class="px-3 pt-3 pb-0.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                {{ $section['group'] }}
            </p>
            @foreach ($visibleItems as $item)
                <x-sidebar-link :href="route($item['route'])" :active="request()->routeIs($item['route'])">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                    </svg>
                    {{ $item['label'] }}
                </x-sidebar-link>
            @endforeach
        @endif
    @else
        <x-sidebar-link :href="route($section['route'])" :active="request()->routeIs($section['route'])">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $section['icon'] }}" />
            </svg>
            {{ $section['label'] }}
        </x-sidebar-link>
    @endif
@endforeach
