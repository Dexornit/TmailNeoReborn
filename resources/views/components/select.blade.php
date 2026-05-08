@props(["disabled" => false, "size" => ""])

@php
    $sizeClasses = match ($size) {
        "xs" => "text-xs",
        "sm" => "text-sm",
        default => "text-base",
    };

    $attributes = $attributes->merge(["class" => "$sizeClasses"]);
@endphp

<select {{ $disabled ? "disabled" : "" }} {!! $attributes->merge(["class" => "neo-select dark:bg-gray-900 dark:text-gray-300"]) !!}>
    {{ $slot }}
</select>
