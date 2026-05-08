@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-semibold text-sm text-[#1F2937]']) }}>
    {{ $value ?? $slot }}
</label>
