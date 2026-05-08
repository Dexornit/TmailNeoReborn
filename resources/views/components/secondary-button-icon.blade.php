@props(["style" => "", "size" => "xs", "icon" => "hgi-arrow-right-01", "position" => "right"])

@php
    $baseClasses = "neo-btn flex gap-1 items-center px-4 py-2 font-bold uppercase tracking-wide focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150";

    $styleClasses = match ($style) {
        "primary" => "neo-btn--secondary-primary text-indigo-700 focus:ring-indigo-500",
        "success" => "neo-btn--secondary-success text-green-700 focus:ring-green-500",
        "error" => "neo-btn--secondary-error text-red-700 focus:ring-red-500",
        "warning" => "neo-btn--secondary-warning text-yellow-700 focus:ring-yellow-400",
        "info" => "neo-btn--secondary-info text-blue-700 focus:ring-blue-500",
        default => "neo-btn--secondary-default text-gray-800 focus:ring-indigo-500",
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
