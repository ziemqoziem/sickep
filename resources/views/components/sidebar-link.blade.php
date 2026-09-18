@props(['active' => false])

@php
$classes = ($active ?? false)
            ? 'flex items-center gap-3 px-3 py-1.5 rounded-lg text-sm font-semibold bg-sky-50 text-sky-700'
            : 'flex items-center gap-3 px-3 py-1.5 rounded-lg text-sm font-medium text-slate-600 hover:bg-sky-50 hover:text-sky-700 transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
