@props(["align" => "right", "width" => "48", "contentClasses" => "py-1 bg-[#FFFBF1]", "dropdownClasses" => ""])

@php
    $alignmentClasses = match ($align) {
        "left" => "ltr:origin-top-left rtl:origin-top-right start-0",
        "top" => "origin-top",
        "none", "false" => "",
        default => "ltr:origin-top-right rtl:origin-top-left end-0",
    };

    $width = match ($width) {
        "48" => "w-48",
        "60" => "w-60",
        "full" => "w-full",
        default => "w-48",
    };
@endphp

<div class="relative" x-data="{ open: false }" @click.away="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute z-50 mt-2 {{ $width }} rounded-none shadow-none border-[3px] border-[#1F2937] {{ $alignmentClasses }} {{ $dropdownClasses }}" style="display: none" @click="open = false">
        <div class="{{ $contentClasses }}" style="box-shadow: 4px 4px 0 #1F2937;">
            {{ $content }}
        </div>
    </div>
</div>
