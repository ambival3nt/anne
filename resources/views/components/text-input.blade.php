@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!!
$attributes->merge(['class' =>'bg-ltblack border border-ltblue/50 text-ltblue  focus-visible:ring-2 focus-visible:ring-ltblue rounded-md focus:border-ltblue-55/50 active:border-ltblue-55/50']) !!}>
