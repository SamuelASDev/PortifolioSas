@props(['value'])

<label {{ $attributes->merge(['class' => 'studio-label']) }}>
    {{ $value ?? $slot }}
</label>
