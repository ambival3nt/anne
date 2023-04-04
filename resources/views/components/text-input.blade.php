@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!!
$attributes->merge(['class' =>
'border focus:border-grey-500 focus:ring-grey-500 rounded-md shadow-sm']) !!}>
