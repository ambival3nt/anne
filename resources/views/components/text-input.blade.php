@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!!
$attributes->merge(['class' =>'px-2 py-2 border border-ltblue/50 bg-ltblack focus:border-ltblue-55 focus-visible:ring focus-visible:ring-ltblue-55 rounded-md active:bg-ltblack focus:outline-none']) !!}>
