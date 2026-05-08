<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-4 text-sm text-[#1F2937]">
            {{ __('Before continuing, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
        </div>

        @if (session("status") == "verification-link-sent")
            <div class="neo-alert neo-alert--ok mb-4 px-4 py-2 font-bold text-sm">
                {{ __("A new verification link has been sent to the email address you provided in your profile settings.") }}
            </div>
        @endif

        <div class="mt-4 flex items-center justify-between">
            <form method="POST" action="{{ route("verification.send") }}">
                @csrf

                <div>
                    <x-button type="submit">
                        {{ __("Resend Verification Email") }}
                    </x-button>
                </div>
            </form>

            <div>
                <form method="POST" action="{{ route("logout") }}" class="inline">
                    @csrf

                    <button type="submit" class="underline text-sm text-[#1F2937] hover:text-black rounded-md focus:outline-none ms-2">
                        {{ __("Log Out") }}
                    </button>
                </form>
            </div>
        </div>
    </x-authentication-card>
</x-guest-layout>
