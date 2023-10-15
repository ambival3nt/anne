@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full pl-3 pr-4 py-2 border-l-4 border-ltblue text-left text-base font-medium text-ltblue focus:outline-none focus:text-ltblue-55 focus:bg-midnight-dk focus:border-ltblue-55 transition duration-150 ease-in-out'
            : 'block w-full pl-3 pr-4 py-2 border-l-4 border-transparent text-left text-base font-medium text-gray-600 hover:text-ltblue hover:bg-midnight-dk hover:border-ltblue focus:outline-none focus:text-ltblue-55 focus:bg-midnight-dk focus:border-ltblue-55 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
