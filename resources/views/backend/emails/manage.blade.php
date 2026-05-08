<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-end gap-3">
        <div>
            <h1 class="text-2xl font-black uppercase tracking-tight">{{ __('Email Management') }}</h1>
            <p class="text-sm text-gray-700 dark:text-gray-300">
                {{ __('Create, list, and manage email addresses available to users.') }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <span class="neo-pill neo-pill--accent">{{ __('Total') }}: {{ $stats['total'] }}</span>
            <span class="neo-pill neo-pill--ok">{{ __('Active') }}: {{ $stats['active'] }}</span>
            <span class="neo-pill neo-pill--warn">{{ __('Disabled') }}: {{ $stats['disabled'] }}</span>
            <span class="neo-pill neo-pill--err">{{ __('Trashed') }}: {{ $stats['trashed'] }}</span>
        </div>
    </div>

    <!-- Generator -->
    <x-card class="p-6">
        <h2 class="text-lg font-black uppercase tracking-wide mb-4">{{ __('Create Emails') }}</h2>

        <!-- Mode tabs -->
        <div class="flex flex-wrap gap-2 mb-6">
            <button type="button" wire:click="setMode('random')"
                class="neo-btn px-4 py-2 text-xs uppercase tracking-wide {{ $mode === 'random' ? 'neo-btn--primary text-black' : 'neo-btn--secondary text-black' }}">
                {{ __('Single Random') }}
            </button>
            <button type="button" wire:click="setMode('bulk_random')"
                class="neo-btn px-4 py-2 text-xs uppercase tracking-wide {{ $mode === 'bulk_random' ? 'neo-btn--primary text-black' : 'neo-btn--secondary text-black' }}">
                {{ __('Bulk Random') }}
            </button>
            <button type="button" wire:click="setMode('manual')"
                class="neo-btn px-4 py-2 text-xs uppercase tracking-wide {{ $mode === 'manual' ? 'neo-btn--primary text-black' : 'neo-btn--secondary text-black' }}">
                {{ __('Manual Usernames') }}
            </button>
        </div>

        <!-- Mode-specific input -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                @if ($mode === 'random')
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        {{ __('Generates a single random email using the selected domain(s).') }}
                    </p>
                @elseif ($mode === 'bulk_random')
                    <div>
                        <x-label for="qty" value="{{ __('Quantity') }}" />
                        <x-input id="qty" type="number" min="1" max="500" class="mt-1 block w-full" wire:model="qty" />
                        <small class="text-xs text-gray-600 dark:text-gray-400">{{ __('Max 500 per batch.') }}</small>
                    </div>
                @else
                    <div>
                        <x-label for="manualList" value="{{ __('Usernames (one per line, or comma/semicolon separated)') }}" />
                        <x-textarea id="manualList" rows="6" class="mt-1 block w-full font-mono text-sm" wire:model="manualList"></x-textarea>
                        <small class="text-xs text-gray-600 dark:text-gray-400">
                            {{ __('Usernames will be paired with selected domains in round-robin fashion.') }}
                        </small>
                    </div>
                @endif
            </div>

            <!-- Domain selector -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <x-label value="{{ __('Domains') }}" />
                    <div class="flex gap-2">
                        <button type="button" wire:click="selectAllDomains" class="text-xs underline font-bold uppercase">
                            {{ __('Select all') }}
                        </button>
                        <button type="button" wire:click="clearDomains" class="text-xs underline font-bold uppercase">
                            {{ __('Clear') }}
                        </button>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 max-h-48 overflow-y-auto p-3 border-[3px] border-black bg-white dark:bg-gray-900">
                    @forelse ($domains as $domain)
                        @php $on = in_array($domain->domain, $selectedDomains, true); @endphp
                        <button type="button" wire:click="toggleDomain('{{ $domain->domain }}')"
                            class="neo-pill {{ $on ? 'neo-pill--ok' : '' }}">
                            {{ $domain->domain }}
                        </button>
                    @empty
                        <span class="text-xs text-gray-600 dark:text-gray-400">
                            {{ __('No active domains. Add a domain first in') }}
                            <a href="{{ route('domains') }}" class="underline font-bold">{{ __('Domains') }}</a>.
                        </span>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap gap-2 justify-end">
            <x-button type="button" wire:click="generate" wire:loading.attr="disabled" wire:target="generate" style="primary">
                <span wire:loading.remove wire:target="generate">{{ __('Generate') }}</span>
                <span wire:loading wire:target="generate"><i class="hgi hgi-stroke hgi-loading-03 hgi-spin mr-1"></i>{{ __('Working...') }}</span>
            </x-button>
        </div>

        @if ($showResults)
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="neo-alert bg-green-50 dark:bg-green-900/30 p-4">
                    <h3 class="font-black uppercase text-sm mb-2">
                        {{ __('Created') }} ({{ count($createdEmails) }})
                    </h3>
                    @if (empty($createdEmails))
                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('No emails created.') }}</p>
                    @else
                        <ul class="text-xs font-mono space-y-1 max-h-48 overflow-y-auto">
                            @foreach ($createdEmails as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div class="neo-alert bg-yellow-50 dark:bg-yellow-900/30 p-4">
                    <h3 class="font-black uppercase text-sm mb-2">
                        {{ __('Skipped') }} ({{ count($skippedEmails) }})
                    </h3>
                    @if (empty($skippedEmails))
                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('Nothing skipped.') }}</p>
                    @else
                        <ul class="text-xs font-mono space-y-1 max-h-48 overflow-y-auto">
                            @foreach ($skippedEmails as $row)
                                <li>{{ $row['username'] ?? '' }} <span class="text-gray-500">— {{ $row['reason'] ?? '' }}</span></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        @endif
    </x-card>

    <!-- List + filters -->
    <x-card class="p-6">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-4">
            <h2 class="text-lg font-black uppercase tracking-wide">{{ __('All Emails') }}</h2>
            <div class="flex flex-col sm:flex-row gap-2">
                <x-input type="text" placeholder="{{ __('Search...') }}" wire:model.live.debounce.300ms="search" class="w-full sm:w-56" />
                <x-select wire:model.live="statusFilter" class="w-full sm:w-40">
                    <option value="">{{ __('All status') }}</option>
                    <option value="active">{{ __('Active') }}</option>
                    <option value="disabled">{{ __('Disabled') }}</option>
                    <option value="trashed">{{ __('Trashed') }}</option>
                </x-select>
                <x-select wire:model.live="domainFilter" class="w-full sm:w-44">
                    <option value="">{{ __('All domains') }}</option>
                    @foreach ($domains as $d)
                        <option value="{{ $d->domain }}">{{ $d->domain }}</option>
                    @endforeach
                </x-select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide font-black border-b-[3px] border-black">
                        <th class="py-2 pr-4">{{ __('Email') }}</th>
                        <th class="py-2 pr-4">{{ __('Domain') }}</th>
                        <th class="py-2 pr-4">{{ __('Status') }}</th>
                        <th class="py-2 pr-4">{{ __('Owner') }}</th>
                        <th class="py-2 pr-4">{{ __('Last used') }}</th>
                        <th class="py-2 pr-4 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($emails as $row)
                        <tr class="neo-row">
                            <td class="py-2 pr-4 font-mono break-all">{{ $row->email }}</td>
                            <td class="py-2 pr-4">{{ $row->domain }}</td>
                            <td class="py-2 pr-4">
                                @if ($row->trashed())
                                    <span class="neo-pill neo-pill--err">{{ __('Trashed') }}</span>
                                @elseif ($row->status === 'disabled')
                                    <span class="neo-pill neo-pill--warn">{{ __('Disabled') }}</span>
                                @else
                                    <span class="neo-pill neo-pill--ok">{{ __('Active') }}</span>
                                @endif
                            </td>
                            <td class="py-2 pr-4 text-xs">
                                {{ $row->user_id ? '#' . $row->user_id : __('shared') }}
                            </td>
                            <td class="py-2 pr-4 text-xs">
                                {{ $row->last_used_at ? $row->last_used_at->diffForHumans() : '—' }}
                            </td>
                            <td class="py-2 pr-4 text-right whitespace-nowrap">
                                @if ($row->trashed())
                                    <button wire:click="enableEmail({{ $row->id }})" class="neo-pill neo-pill--ok">{{ __('Restore') }}</button>
                                @elseif ($row->status === 'disabled')
                                    <button wire:click="enableEmail({{ $row->id }})" class="neo-pill neo-pill--ok">{{ __('Enable') }}</button>
                                    <button wire:click="$dispatch('confirm-trash', '{{ $row->id }}')" class="neo-pill neo-pill--err">{{ __('Trash') }}</button>
                                @else
                                    <button wire:click="disableEmail({{ $row->id }})" class="neo-pill neo-pill--warn">{{ __('Disable') }}</button>
                                    <button wire:click="$dispatch('confirm-trash', '{{ $row->id }}')" class="neo-pill neo-pill--err">{{ __('Trash') }}</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-gray-500">{{ __('No emails yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $emails->links() }}
        </div>
    </x-card>

    <!-- Per-domain stats -->
    @if (!empty($stats['per_domain']))
        <x-card class="p-6">
            <h2 class="text-lg font-black uppercase tracking-wide mb-4">{{ __('Active emails per domain') }}</h2>
            <div class="flex flex-wrap gap-2">
                @foreach ($stats['per_domain'] as $row)
                    <span class="neo-pill neo-pill--info">{{ $row['domain'] }}: {{ $row['total'] }}</span>
                @endforeach
            </div>
        </x-card>
    @endif

    @script
        <script>
            $wire.on('confirm-trash', (id) => {
                Swal.fire({
                    title: '{{ __("Move to trash?") }}',
                    text: '{{ __("This email will be soft-deleted and can be restored later.") }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: '{{ __("Yes, trash it") }}',
                    cancelButtonText: '{{ __("Cancel") }}',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $wire.call('softDeleteEmail', id);
                    }
                });
            });

            window.addEventListener('showAlert', (event) => {
                const detail = event.detail[0] || event.detail;
                Swal.fire({
                    icon: detail.type || 'info',
                    title: detail.message || '',
                    toast: true,
                    position: 'top-end',
                    timer: 2500,
                    showConfirmButton: false,
                });
            });
        </script>
    @endscript
</div>
