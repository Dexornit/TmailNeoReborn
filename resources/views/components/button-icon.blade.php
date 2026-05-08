@props(["style" => "", "size" => "xs", "icon" => "hgi-arrow-right-01", "position" => "right"])

@php
    $baseClasses = "neo-btn flex gap-1 items-center px-4 py-2 font-bold uppercase tracking-wide focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150";

    $styleClasses = match ($style) {
        "primary" => "neo-btn--primary text-black focus:ring-indigo-500",
        "success" => "neo-btn--success text-black focus:ring-green-500",
        "error" => "neo-btn--error text-black focus:ring-red-500",
        "warning" => "neo-btn--warning text-black focus:ring-yellow-400",
        "info" => "neo-btn--info text-black focus:ring-blue-500",
        default => "neo-btn--default text-black focus:ring-indigo-500",
    };

    $sizeClasses = match ($size) {
        "xs" => "text-xs",
        "sm" => "text-sm",
        "md" => "text-base",
        "lg" => "text-lg",
        "xl" => "text-xl",
        default => "",
    };
@endphp

<button {{ $attributes->merge(["type" => "submit", "class" => "$baseClasses $styleClasses $sizeClasses"]) }}>
    @if ($position == "left")
        <i class="hgi hgi-stroke {{ $icon }}"></i>
    @endif

    {{ $slot }}
    @if ($position == "right")
        <i class="hgi hgi-stroke {{ $icon }}"></i>
    @endif
</button>
