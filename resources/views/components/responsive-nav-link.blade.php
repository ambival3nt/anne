@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full pl-3 pr-4 py-2 border-l-4 border-ltblue-55 text-left text-base font-medium text-ltblue-55 bg-midnight focus:outline-none focus:text-ltblue focus:bg-midnight-900 focus:border-ltblue-55 transition duration-150 ease-in-out'
            : 'block w-full pl-3 pr-4 py-2 border-l-4 border-transparent text-left text-base font-medium text-ltblue-55 hover:text-ltblue-55 hover:bg-midnight hover:border-ltblue focus:outline-none focus:text-ltblue focus:bg-midnight focus:border-ltblue-55 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
