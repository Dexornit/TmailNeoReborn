@section("title", "Email Management")

<x-backend-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            {{ __("Email Management") }}
        </h2>
    </x-slot>

    @livewire("backend.emails.manage")
</x-backend-layout>
