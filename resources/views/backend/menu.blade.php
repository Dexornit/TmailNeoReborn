@section("title", "Manage Menu")

<x-backend-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            {{ __("Menu") }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 px-3 sm:px-6 lg:px-8">
            @livewire("backend.menu.manage", ["location" => "primary"])
        </div>
        <div class="max-w-7xl mx-auto py-10 px-3 sm:px-6 lg:px-8">
            @livewire("backend.menu.manage", ["location" => "secondary"])
        </div>
    </div>
</x-backend-layout>
