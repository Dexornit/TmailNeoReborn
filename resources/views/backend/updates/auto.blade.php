<x-form-section submit="apply">
    <x-slot name="title">
        {{ __("Auto Updates") }}
    </x-slot>

    <x-slot name="description">
        {{ __("You can apply lastest updates automatically from here if available.") }}
        <br />
        @if (! $status["available"])
            <x-button id="check-for-updates" class="mt-4 flex items-center gap-2" type="button" wire:click="checkForUpdates">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 animate-spin pause-spinner">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                {{ __("Check for Updates") }}
            </x-button>
        @endif
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6">
            <div class="w-full py-3 px-4 overflow-hidden rounded-xl flex items-center border {{ $status["available"] ? "bg-blue-50 dark:bg-blue-900" : "bg-gray-50 dark:bg-gray-900" }} {{ $status["available"] ? "border-blue-500" : "border-gray-500" }}">
                <div class="{{ $status["available"] ? "text-blue-500" : "text-gray-500" }} w-10">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <div class="ml-4 flex-1">
                    <div class="text-lg font-semibold">{{ __($status["available"] ? "Update Available" : 'You\'re on Latest Version') }}</div>
                    <div class="text-sm">{!! $status["message"] !!}</div>
                </div>
            </div>
        </div>
        @if ($progress)
            <div id="progress-container" class="col-span-6 bg-black px-5 py-4 rounded-xl max-h-96 overflow-scroll">
                {!! $progress !!}
            </div>
            <script>
                const progressContainer = document.getElementById('progress-container');
                progressContainer.scrollTop = progressContainer.scrollHeight;
            </script>
        @endif
    </x-slot>

    <x-slot name="actions">
        @if ($status["available"] && ! $status["disabled"])
            <x-button>
                {{ __("Apply") }}
            </x-button>
        @elseif ($status["disabled"])
            <small class="text-red-500 font-bold">{{ __("Please upgrade your PHP version to apply update.") }}</small>
        @else
            <x-button disabled>
                {{ __("Apply") }}
            </x-button>
        @endif
    </x-slot>
</x-form-section>

@script
    <script>
        if (document.querySelector('#check-for-updates')) {
            document.querySelector('#check-for-updates').addEventListener('click', () => {
                document.querySelector('#check-for-updates').disabled = true;
                document.querySelector('#check-for-updates svg').classList.remove('pause-spinner');
            });
        }
    </script>
@endscript
