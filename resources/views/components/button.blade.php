@props(["style" => "", "size" => "xs"])

@php
    $baseClasses = "neo-btn inline-flex items-center px-4 py-2 font-bold uppercase tracking-wide focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150";

    $styleClasses = match ($style) {
        "primary" => "neo-btn--primary text-white hover:bg-indigo-700 focus:ring-indigo-500",
        "success" => "neo-btn--success text-black hover:brightness-95 focus:ring-green-500",
        "error" => "neo-btn--error text-white hover:brightness-95 focus:ring-red-500",
        "warning" => "neo-btn--warning text-black hover:brightness-95 focus:ring-yellow-400",
        "info" => "neo-btn--info text-black hover:brightness-95 focus:ring-blue-500",
        default => "neo-btn--default text-black hover:brightness-95 focus:ring-indigo-500",
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
    {{ $slot }}
</button>
