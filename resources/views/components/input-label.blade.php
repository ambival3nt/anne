@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium  text-ltblue']) }}>
    {{ $value ?? $slot }}
</label>
