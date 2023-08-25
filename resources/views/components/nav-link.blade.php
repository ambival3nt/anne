@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex 
                items-center 
                px-1 
                pt-1 
                border-b-2 
                border-ltblue-55 
                text-base 
                font-medium 
                leading-5 
                text-ltblue-55 
                focus:bg-black/30
                focus:outline-none 
                focus:border-ltblue-75 
                transition 
                duration-200 
                ease-in-out'

            : 'inline-flex 
                items-center 
                px-1 
                pt-1 
                border-b-2 
                border-transparent 
                text-base 
                font-medium 
                leading-5 
                text-ltblue 
                hover:text-ltblue-55 
                hover:border-ltblue-55 
                focus:outline-none 
                focus:bg-black/30
                focus:text-ltblue-75 
                focus:border-ltblue-75 
                transition duration-200 
                ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
