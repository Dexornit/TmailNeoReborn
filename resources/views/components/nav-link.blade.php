@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-[3px] border-[#EB4C4C] text-sm font-bold leading-5 text-[#1F2937] focus:outline-none transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-[3px] border-transparent text-sm font-medium leading-5 text-[#1F2937] hover:text-black hover:border-[#1F2937] focus:outline-none focus:text-black focus:border-[#1F2937] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
