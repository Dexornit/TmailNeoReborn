<x-backend-layout>
    <x-slot name="title">{{ __('Maintenance') }}</x-slot>

    <div class="py-6 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <div>
            <h1 class="text-2xl font-bold">{{ __('Maintenance & Health Check') }}</h1>
            <p class="text-sm text-gray-600 mt-1">
                {{ __('Run common ops tasks without needing terminal access. Useful on shared hosting.') }}
            </p>
        </div>

        @if ($message)
            <div class="border-2 border-green-600 bg-green-50 px-4 py-3 rounded text-sm">
                <pre class="whitespace-pre-wrap">{{ $message }}</pre>
            </div>
        @endif

        @if ($error)
            <div class="border-2 border-red-600 bg-red-50 px-4 py-3 rounded text-sm">
                <strong>{{ __('Error:') }}</strong> {{ $error }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <button wire:click="migrate" wire:loading.attr="disabled" class="neo-btn neo-btn--primary p-4 text-left">
                <i class="hgi hgi-stroke hgi-database-02"></i>
                <div class="font-semibold">{{ __('Run Migrations') }}</div>
                <div class="text-xs font-normal opacity-80 mt-1">{{ __('php artisan migrate --force') }}</div>
            </button>
            <button wire:click="clearCache" wire:loading.attr="disabled" class="neo-btn neo-btn--info p-4 text-left">
                <i class="hgi hgi-stroke hgi-broom"></i>
                <div class="font-semibold">{{ __('Clear All Caches') }}</div>
                <div class="text-xs font-normal opacity-80 mt-1">{{ __('php artisan optimize:clear') }}</div>
            </button>
            <button wire:click="storageLink" wire:loading.attr="disabled" class="neo-btn neo-btn--success p-4 text-left">
                <i class="hgi hgi-stroke hgi-link-04"></i>
                <div class="font-semibold">{{ __('Recreate Storage Symlink') }}</div>
                <div class="text-xs font-normal opacity-80 mt-1">{{ __('php artisan storage:link --force') }}</div>
            </button>
        </div>

        <div class="neo-card overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b-2 border-black bg-gray-50">
                <h2 class="font-bold">{{ __('Health Checks') }}</h2>
                <button wire:click="refreshChecks" class="text-xs underline">{{ __('Refresh') }}</button>
            </div>
            <div>
                @foreach ($checks as $check)
                    <div class="flex items-start gap-3 px-4 py-3 border-b last:border-b-0 border-black/10 {{ $check['ok'] ? 'bg-green-50' : 'bg-red-50' }}">
                        <div class="mt-1 text-lg">
                            @if ($check['ok'])
                                <span class="text-green-600">&#10003;</span>
                            @else
                                <span class="text-red-600">&#10007;</span>
                            @endif
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-sm">{{ $check['name'] }}</div>
                            <div class="text-xs text-gray-600 mt-0.5 break-all">{{ $check['detail'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div wire:loading wire:target="migrate,clearCache,storageLink,refreshChecks" class="text-sm text-gray-600">
            <i class="hgi hgi-stroke hgi-loading-03 hgi-spin"></i>
            {{ __('Working...') }}
        </div>

    </div>
</x-backend-layout>
