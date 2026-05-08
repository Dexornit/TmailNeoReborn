@section("title", "Domains")

<x-backend-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            {{ __("Domains") }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 px-3 sm:px-6 lg:px-8">
            @livewire("backend.domains.manage")
        </div>
    </div>
</x-backend-layout>
