@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-ltblue']) }}>
    {{ $value ?? $slot }}
</label>
