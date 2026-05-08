@section("title", __("TMail - Installer"))

<x-guest-layout>
    <main class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="neo-card overflow-hidden">
                <div class="bg-[#FCF596] border-b-[3px] border-[#1F2937] pt-10 pb-6 px-6 text-center">
                    <img class="m-auto max-w-48" src="{{ asset("images/installer-logo-light.png") }}" alt="TMail Installer" />
                    <h1 class="mt-4 font-black text-2xl uppercase tracking-widest text-[#1F2937]">{{ __("Installer") }}</h1>
                </div>
                <div class="p-10 bg-[#FFFBF1]">
                    @livewire("installer.installer")
                </div>
            </div>
        </div>
    </main>
</x-guest-layout>
