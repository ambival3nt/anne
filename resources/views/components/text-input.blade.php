@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!!
$attributes->merge(['class' =>
'bg-violet1 border focus:border-gray-500 focus:ring-gray-500 rounded-md shadow-sm']) !!}>
