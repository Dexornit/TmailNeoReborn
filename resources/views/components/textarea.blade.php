@props(["disabled" => false])

<textarea {{ $disabled ? "disabled" : "" }} {!! $attributes->merge(["class" => "neo-textarea dark:bg-gray-900 dark:text-gray-300"]) !!}></textarea>
