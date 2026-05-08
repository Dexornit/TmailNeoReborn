@php
    $content = [];
@endphp

<x-frontend-layout :content="$content">
    <div class="neobrutalism-theme min-h-screen flex flex-col">
        <header class="neo-header order-1 border-b-[3px] border-black">
            <div class="container mx-auto">
                <div class="flex items-center pt-8 pb-4 px-6 lg:pt-0 lg:pb-0 mb-2 lg:mb-0">
                    <a href="{{ Util::localizeRoute('home') }}">
                        @if (config('app.settings.logo') && Illuminate\Support\Facades\Storage::disk('public')->has(config('app.settings.logo')))
                            <img class="h-10 lg:h-12 w-auto" src="{{ url('storage/' . config('app.settings.logo')) }}" alt="logo" />
                        @elseif (Illuminate\Support\Facades\Storage::disk('public')->has('images/custom-logo.png'))
                            <img class="h-10 lg:h-12 w-auto" src="{{ url('storage/images/custom-logo.png') }}" alt="logo" />
                        @else
                            <div class="neo-logo relative">
                                {{ config('app.settings.name') }}
                                <div class="neo-star absolute -top-2 -right-5"></div>
                            </div>
                        @endif
                    </a>
                    <div class="flex-1">
                        @livewire('frontend.nav')
                    </div>
                </div>
            </div>
        </header>

        <div class="grow container mx-auto order-2 flex flex-col md:flex-row md:space-x-2 justify-center pt-6 pb-10 px-4 md:px-6 relative z-10">
            @livewire('frontend.public-landing')
        </div>

        <footer class="order-3 border-t-[3px] border-black py-4 px-4 md:px-6 neo-footer">
            <div class="container mx-auto">
                <div class="flex flex-col lg:flex-row gap-3 justify-between items-center text-xs md:text-sm font-bold">
                    <div class="opacity-75 text-center break-words">{{ __('Copyright') }} &copy; {{ date('Y') }} {{ config('app.settings.name') }}. {{ __('All rights reserved.') }}</div>
                </div>
            </div>
        </footer>
    </div>
</x-frontend-layout>
