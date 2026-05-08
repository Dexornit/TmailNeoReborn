<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="neo-card p-6 md:p-8 relative">
        <div class="neo-star absolute -top-4 -left-4 w-8 h-8 z-10"></div>

        <h1 class="text-2xl md:text-3xl font-black uppercase tracking-tight mb-2">
            {{ __('Access your inbox') }}
        </h1>
        <p class="text-sm md:text-base text-gray-700 mb-6 font-bold">
            {{ __('Type the email address that was created for you. You can read OTPs, verify codes, and refresh new mail.') }}
        </p>

        <form wire:submit.prevent="submit" class="space-y-4">
            <div>
                <label for="emailInput" class="block text-xs font-black uppercase tracking-wide mb-1">
                    {{ __('Email address') }}
                </label>
                <input
                    id="emailInput"
                    type="email"
                    autocomplete="off"
                    autocapitalize="none"
                    spellcheck="false"
                    wire:model.defer="emailInput"
                    class="neo-input w-full"
                    placeholder="hulaksa@wanseven.com"
                    required
                />
                @error('emailInput')
                    <p class="mt-1 text-xs font-bold text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                <button type="submit" class="neo-btn" style="background-color: #A3E4D7;">
                    <i class="fas fa-envelope-open-text"></i>
                    <span>{{ __('Open inbox') }}</span>
                </button>

                @auth
                    <a href="{{ Util::localizeRoute('mailbox') }}" class="text-xs font-bold uppercase underline">
                        {{ __('Or go to your dashboard') }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-xs font-bold uppercase underline">
                        {{ __('Login to create or delete emails') }}
                    </a>
                @endauth
            </div>
        </form>

        @if (!empty($domains))
            <div class="mt-6 pt-6 border-t-[3px] border-black">
                <div class="text-xs font-black uppercase tracking-wide mb-2">
                    {{ __('Available domains') }}
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($domains as $d)
                        <span class="neo-badge" style="background-color: #AED6F1; color:#000;">
                            @ {{ $d }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-6 pt-4 border-t-[3px] border-black text-xs text-gray-700">
            <p class="font-bold mb-1">{{ __('Tip') }}:</p>
            <p>
                {{ __('You can also share a direct link like') }}
                <code class="font-mono bg-yellow-100 px-1 border-2 border-black">{{ url('/mailbox/hulaksa@wanseven.com') }}</code>
                {{ __('to jump straight to that inbox.') }}
            </p>
        </div>
    </div>
</div>
