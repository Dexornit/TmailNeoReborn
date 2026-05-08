@props(["disabled" => false, "id" => "id"])

<div class="relative neo-toggle">
    <input {{ $disabled ? "disabled" : "" }} id="{{ $id }}" type="checkbox" {!! $attributes->merge(["class" => "hidden"]) !!} />
    <div class="toggle-path neo-toggle-path bg-gray-200 dark:bg-gray-600 w-9 h-5 shadow-inner"></div>
    <div class="toggle-circle neo-toggle-circle absolute w-3.5 h-3.5 bg-white shadow inset-y-0 left-0"></div>
</div>
