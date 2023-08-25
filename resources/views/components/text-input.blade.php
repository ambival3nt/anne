@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!!
$attributes->merge(['class' =>'
border
border-ltblue/50 
bg-ltblack
focus:border-ltblue-55 
focus:ring-ltblue-55 
rounded-md 
active:bg-ltblack
active:ouline-ltblue
']) !!}>
