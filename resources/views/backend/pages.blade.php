@section("title", "Pages")

<x-backend-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            {{ __("Pages") }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 px-3 sm:px-6 lg:px-8">
            @livewire("backend.pages.manage")
        </div>
    </div>
</x-backend-layout>
