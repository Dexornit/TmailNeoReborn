<div x-data="{ in_app: {{ $in_app ? 'true' : 'false' }} }">
    <div>
        {{-- ═══════════ GUEST: EMAIL INPUT (no session email yet) ═══════════ --}}
        @if ($isGuest && ! $hasEmail)
            <div class="guest-actions mt-4 px-6">
                <form wire:submit.prevent="openInbox" class="neo-guest-form">
                    <div class="neo-card p-5 md:p-6 relative">
                        <div class="neo-star absolute -top-4 -left-4 w-8 h-8 z-10"></div>

                        <label for="emailInput" class="block text-xs font-black uppercase tracking-wide mb-2">
                            {{ __('Enter your email address') }}
                        </label>
                        <div class="flex flex-col md:flex-row gap-3">
                            <input
                                id="emailInput"
                                type="email"
                                autocomplete="off"
                                autocapitalize="none"
                                spellcheck="false"
                                wire:model.defer="emailInput"
                                class="neo-input flex-1"
                                placeholder="hulaksa@{{ $publicDomains[0] ?? 'example.com' }}"
                                required
                            />
                            <button type="submit" class="neo-btn neo-btn--success px-6">
                                <i class="fas fa-envelope-open-text"></i>
                                <span>{{ __('Open inbox') }}</span>
                            </button>
                        </div>

                        <p class="mt-3 text-xs font-bold opacity-60">
                            {{ __('Type the email address that was created for you. You can read OTPs, verify codes, and refresh new mail.') }}
                        </p>

                        @if (! empty($publicDomains))
                            <div class="mt-4 pt-4 border-t-[3px] border-black">
                                <div class="text-xs font-black uppercase tracking-wide mb-2">{{ __('Available domains') }}</div>
                                <div class="neo-domain-list">
                                    @foreach ($publicDomains as $d)
                                        <span class="neo-pill neo-pill--info">@ {{ $d }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="mt-4 text-xs font-bold opacity-60">
                            {{ __('Or use a direct link like') }}
                            <code class="neo-code">{{ url('/mailbox/hulaksa@' . ($publicDomains[0] ?? 'example.com')) }}</code>
                        </div>

                        <div class="mt-4 pt-4 border-t-[3px] border-black flex justify-end">
                            <a href="{{ route('login') }}" class="text-xs font-black uppercase underline">
                                {{ __('Login to create or delete emails') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        @else
            {{-- ═══════════ CREATE / RANDOM FORM (logged-in or legacy guest) ═══════════ --}}
            <div x-show.transition.in="in_app && {{ $canCreate ? 'true' : 'false' }}" class="app-action mt-4 px-6" style="display: none">
                @if (config('app.settings.captcha') == 'hcaptcha' || config('app.settings.captcha') == 'recaptcha2')
                    <div class="flex items-center justify-center">
                        <x-captcha field="captcha" />
                    </div>
                @endif

                <form wire:submit.prevent="create" method="post">
                    <div class="max-w-screen-md mx-auto flex flex-col gap-3">
                        @if (count($emails) > 0 && $in_app)
                            <a href="{{ Util::localizeRoute('mailbox') }}" class="neo-btn neo-btn--success self-start">
                                <i class="fas fa-angle-double-left"></i>
                                <span>{{ __('Get back to MailBox') }}</span>
                            </a>
                        @endif

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
                                                " class="block px-4 py-2 text-sm leading-5 text-gray-700 cursor-pointer hover:bg-yellow-200 focus:outline-none transition duration-150 ease-in-out">{{ $domain }}</a>
                                            @endforeach

                                            @foreach ($memberDomains as $domain)
                                                <a class="cursor-not-allowed flex justify-between px-4 py-2 text-sm leading-5 text-gray-400">
                                                    <span>{{ $domain }}</span>
                                                    <span class="neo-pill">{{ __('Member Only') }}</span>
                                                </a>
                                            @endforeach
                                        </x-slot>
                                    </x-dropdown>
                                </div>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-black">
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
                    @if (! $in_app)
                        <button type="button" x-on:click="in_app = false" class="neo-btn neo-btn--secondary ml-2"><i class="fas fa-times"></i></button>
                    @endif
                </form>
            </div>

            {{-- ═══════════ EMAIL ADDRESS DISPLAY + BUTTONS ═══════════ --}}
            <div x-show.transition.in="!in_app || !{{ $canCreate ? 'true' : 'false' }}" class="in-app-actions mt-4 px-6" style="display: none">
                <form class="max-w-screen-md mx-auto" action="#" method="post">
                    <div class="relative">
                        @if ($isGuest)
                            {{-- Guests don't get a switcher dropdown — show the bare email --}}
                            <div class="neo-card p-4 text-center text-lg md:text-2xl font-black tracking-tight relative" id="email_id">
                                <div class="neo-star absolute -top-4 -left-4 w-8 h-8 z-10"></div>
                                {{ $email ?: __('Loading...') }}
                            </div>
                        @else
                            <x-dropdown align="top" width="full">
                                <x-slot name="trigger">
                                    <div class="neo-card p-4 text-center text-lg md:text-2xl font-black tracking-tight cursor-pointer select-none relative" id="email_id">
                                        <div class="neo-star absolute -top-4 -left-4 w-8 h-8 z-10"></div>
                                        {{ $email ?: __('Generating Email...') }}
                                    </div>
                                </x-slot>
                                <x-slot name="content">
                                    @foreach ($emails as $item)
                                        <x-dropdown-link href="{{ route('switch', $item) }}">
                                            {{ $item }}
                                        </x-dropdown-link>
                                    @endforeach
                                </x-slot>
                            </x-dropdown>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-black">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" /></svg>
                            </div>
                        @endif
                    </div>
                </form>
                <div class="divider mt-4"></div>

                @if ($isGuest)
                    {{-- ═══════════ GUEST: only Copy + Refresh ═══════════ --}}
                    <div class="grid grid-cols-2 gap-2 max-w-screen-md mx-auto">
                        <div class="btn_copy neo-btn neo-btn--success justify-center py-3">
                            <i class="far fa-copy"></i>
                            <span>{{ __('Copy') }}</span>
                        </div>
                        <div onclick="
                            document.getElementById('refresh').classList.remove('pause-spinner');
                            if(typeof _isFetching !== 'undefined') _isFetching = false;
                            Livewire.dispatch('fetchMessages');
                        " class="neo-btn neo-btn--info justify-center py-3">
                            <i id="refresh" class="fas fa-sync-alt fa-spin-fast pause-spinner"></i>
                            <span>{{ __('Refresh') }}</span>
                        </div>
                    </div>
                    <div class="max-w-screen-md mx-auto mt-3 flex flex-col sm:flex-row justify-between gap-2 text-xs font-black uppercase">
                        <a href="{{ Util::localizeRoute('home') }}" class="underline opacity-70 hover:opacity-100">
                            <i class="fas fa-search"></i>
                            {{ __('Open another inbox') }}
                        </a>
                        <a href="{{ route('login') }}" class="underline opacity-70 hover:opacity-100">
                            <i class="fas fa-sign-in-alt"></i>
                            {{ __('Login to create or delete') }}
                        </a>
                    </div>
                @else
                    {{-- ═══════════ LOGGED-IN: full Copy + Refresh + New + Delete ═══════════ --}}
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 max-w-screen-md mx-auto">
                        <div class="btn_copy neo-btn neo-btn--success justify-center py-3">
                            <i class="far fa-copy"></i>
                            <span>{{ __('Copy') }}</span>
                        </div>
                        <div onclick="
                            document.getElementById('refresh').classList.remove('pause-spinner');
                            if(typeof _isFetching !== 'undefined') _isFetching = false;
                            Livewire.dispatch('fetchMessages');
                        " class="neo-btn neo-btn--info justify-center py-3">
                            <i id="refresh" class="fas fa-sync-alt fa-spin-fast pause-spinner"></i>
                            <span>{{ __('Refresh') }}</span>
                        </div>
                        <div x-on:click="in_app = true" class="neo-btn neo-btn--accent justify-center py-3">
                            <i class="far fa-plus-square"></i>
                            <span>{{ __('New') }}</span>
                        </div>
                        <div wire:click="deleteEmail" class="neo-btn neo-btn--danger justify-center py-3">
                            <i class="far fa-trash-alt"></i>
                            <span>{{ __('Delete') }}</span>
                        </div>
                    </div>
                @endif
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
