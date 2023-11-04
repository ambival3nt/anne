@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!!
$attributes->merge(['class' =>'px-2 py-2 border border-midnight-300 bg-midnight focus:border-ltblue-55 focus-visible:ring-1 focus-visible:ring-ltblue-55 rounded-md focus:outline-none' . ($disabled ? ' opacity-25' : '')]) !!}>
