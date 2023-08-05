@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-waterloo']) }}>
    {{ $value ?? $slot }}
</label>
