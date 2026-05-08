@props(["disabled" => false, "size" => ""])

@php
    $sizeClasses = match ($size) {
        "xs" => "text-xs",
        "sm" => "text-sm",
        default => "text-base",
    };

    $attributes = $attributes->merge(["class" => "$sizeClasses"]);
@endphp

<input {{ $disabled ? "disabled" : "" }} {!! $attributes->merge(["class" => "neo-input dark:bg-gray-900 dark:text-gray-300 disabled:bg-gray-100 disabled:dark:bg-gray-700"]) !!} />
