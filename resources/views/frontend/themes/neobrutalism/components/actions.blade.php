<div x-data="{ in_app: {{ $in_app ? 'true' : 'false' }} }">
    <div class="neo-actions mt-4 px-4 md:px-6">
        {{-- ═══════════════ MAILBOX BAR (always rendered) ═══════════════
             A single card containing:
               - Email input (user can change it freely)
               - Open Inbox button (validate + sync into App + fetchMessages)
               - Copy / Refresh buttons below (and New / Delete for logged-in)
        --}}
        <form wire:submit.prevent="openInbox" class="neo-mailbox-bar">
            <div class="neo-card p-5 md:p-6 relative">
                <div class="neo-star absolute -top-4 -left-4 w-8 h-8 z-10"></div>

                <div class="flex flex-col md:flex-row gap-3 items-stretch">
                    <input
                        id="emailInput"
                        type="email"
                        autocomplete="off"
                        autocapitalize="none"
                        spellcheck="false"
                        wire:model.defer="emailInput"
                        class="neo-input neo-mailbox-input flex-1"
                        placeholder="{{ __('Masukkan email') }}"
                        required
                    />
                    <button type="submit" class="neo-btn neo-btn--success px-6 whitespace-nowrap">
                        <i class="fas fa-envelope-open-text"></i>
                        <span>{{ __('Open Inbox') }}</span>
                    </button>
                </div>

                {{-- Hidden node carrying the active email — kept stable for
                     the legacy copy handler in resources/js/scripts.js
                     (it reads document.getElementById('email_id').innerText). --}}
                <span id="email_id" class="hidden">{{ $email }}</span>

                @if ($email || ! $isGuest)
                    <div class="grid {{ $isGuest ? 'grid-cols-2' : 'grid-cols-2 lg:grid-cols-4' }} gap-2 mt-4">
                        <button type="button" class="btn_copy neo-btn neo-btn--success justify-center py-3" {{ ! $email ? 'disabled' : '' }}>
                            <i class="far fa-copy"></i>
                            <span>{{ __('Copy') }}</span>
                        </button>

                        <button type="button"
                            onclick="
                                document.getElementById('refresh').classList.remove('pause-spinner');
                                if (typeof _isFetching !== 'undefined') _isFetching = false;
                                Livewire.dispatch('fetchMessages');
                            "
                            class="neo-btn neo-btn--info justify-center py-3" {{ ! $email ? 'disabled' : '' }}>
                            <i id="refresh" class="fas fa-sync-alt fa-spin-fast pause-spinner"></i>
                            <span>{{ __('Refresh') }}</span>
                        </button>

                        @if (! $isGuest)
                            <button type="button" x-on:click="in_app = true" class="neo-btn neo-btn--accent justify-center py-3">
                                <i class="far fa-plus-square"></i>
                                <span>{{ __('New') }}</span>
                            </button>

                            <button type="button" wire:click="deleteEmail" class="neo-btn neo-btn--danger justify-center py-3" {{ ! $email ? 'disabled' : '' }}>
                                <i class="far fa-trash-alt"></i>
                                <span>{{ __('Delete') }}</span>
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </form>

        {{-- ═══════════════ LOGGED-IN: NEW EMAIL CREATION FORM ═══════════════
             Hidden by default, toggled by clicking the "New" button above.
        --}}
        @if (! $isGuest && $canCreate)
            <div x-show.transition.in="in_app" class="neo-create-form mt-4" style="display: none">
                @if (config('app.settings.captcha') == 'hcaptcha' || config('app.settings.captcha') == 'recaptcha2')
                    <div class="flex items-center justify-center mb-3">
                        <x-captcha field="captcha" />
                    </div>
                @endif

                <form wire:submit.prevent="create" method="post">
                    <div class="max-w-screen-md mx-auto flex flex-col gap-3">
                        <div class="flex justify-end">
                            <button type="button" x-on:click="in_app = false" class="neo-btn neo-btn--default text-xs py-1 px-3">
                                <i class="fas fa-times"></i>
                                <span>{{ __('Close') }}</span>
                            </button>
                        </div>

                        <div class="neo-card">
                            <input class="neo-input border-0" type="text" name="user" id="user" wire:model.defer="user" placeholder="{{ __('Enter Username') }}" />
                        </div>

                        <div class="flex flex-col md:flex-row items-stretch gap-3">
                            <div class="relative flex-1">
                                <div class="neo-card" style="overflow: visible !important;">
                                    <x-dropdown width="full">
                                        <x-slot name="trigger">
                                            <input x-ref="domain" type="text" class="neo-input border-0 cursor-pointer pr-10" placeholder="{{ __('Select Domain') }}" name="domain" id="domain" wire:model="domain" readonly />
                                        </x-slot>
                                        <x-slot name="content">
                                            @foreach ($domains as $domain)
                                                <a x-on:click="
                                                    $refs.domain.value = '{{ $domain }}'
                                                    $wire.setDomain('{{ $domain }}')
                                                " class="block px-4 py-2 text-sm leading-5 cursor-pointer hover:bg-[var(--neo-muted)] focus:outline-none transition duration-150 ease-in-out">{{ $domain }}</a>
                                            @endforeach

                                            @foreach ($memberDomains as $domain)
                                                <span class="cursor-not-allowed flex justify-between px-4 py-2 text-sm leading-5 opacity-50">
                                                    <span>{{ $domain }}</span>
                                                    <span class="neo-pill">{{ __('Member Only') }}</span>
                                                </span>
                                            @endforeach
                                        </x-slot>
                                    </x-dropdown>
                                </div>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" /></svg>
                                </div>
                            </div>
                            <input id="create" class="neo-btn neo-btn--success" type="submit" value="{{ __('Create') }}" />
                        </div>
                    </div>
                </form>

                <div class="py-2 text-center font-bold opacity-40 uppercase text-sm">{{ __('or') }}</div>

                <form wire:submit.prevent="random" class="flex justify-center mb-1" method="post">
                    <input id="random" class="neo-btn neo-btn--warning" type="submit" value="{{ __('Create a Random Email') }}" />
                </form>
            </div>
        @endif
    </div>

    @if (config('app.settings.captcha') == 'recaptcha3')
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('app.settings.recaptcha3.site_key') }}"></script>
        <script>
            const handle = (e) => {
                e.preventDefault();
                grecaptcha.ready(function () {
                    grecaptcha.execute('{{ config("app.settings.recaptcha3.site_key") }}', { action: 'submit' }).then(function (token) {
                        Livewire.dispatch('checkReCaptcha3', {
                            token,
                            action: e.target.id,
                        });
                    });
                });
            };
            const _createBtn = document.getElementById('create');
            const _randomBtn = document.getElementById('random');
            if (_createBtn) _createBtn.addEventListener('click', handle);
            if (_randomBtn) _randomBtn.addEventListener('click', handle);
        </script>
    @endif
</div>
