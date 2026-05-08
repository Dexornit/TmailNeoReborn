<nav x-data="{ open: false }" class="neo-admin-nav border-b-[3px] border-black">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route("dashboard") }}">
                        <x-application-mark class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                @if (auth()->user()->role == 7)
                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                        @foreach ([
                                "dashboard" => "Dashboard",
                                "emails" => "Emails",
                                "domains" => "Domains",
                                "pages" => "Pages",
                                "blog" => "Blog",
                                "menu" => "Menu",
                                "settings" => "Settings"
                            ]
                            as $key => $value)
                            <x-nav-link href="{{ route($key) }}" :active="request()->routeIs($key)">
                                {{ __($value) }}
                            </x-nav-link>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="hidden sm:gap-3 sm:flex sm:items-center sm:ms-6">
                <!-- Settings Dropdown -->
                <div class="relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                    <img class="size-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                                </button>
                            @else
                                <span class="inline-flex rounded-none">
                                    <button type="button" class="inline-flex items-center px-3 py-2 border-2 border-[#1F2937] text-sm leading-4 font-bold text-[#1F2937] bg-[#FFFBF1] hover:bg-[#FCF596] focus:outline-none transition ease-in-out duration-150 neo-btn">
                                        {{ Auth::user()->name }}

                                        <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </span>
                            @endif
                        </x-slot>

                        <x-slot name="content">
                            @if (auth()->user()->role == 7)
                                <div class="block px-4 py-2 text-xs text-gray-400">
                                    {{ __("Advance") }}
                                </div>
                                <x-dropdown-link href="{{ route('users') }}">
                                    {{ __("Manage Users") }}
                                </x-dropdown-link>
                                <x-dropdown-link href="{{ route('themes') }}">
                                    {{ __("Themes") }}
                                </x-dropdown-link>
                                <x-dropdown-link href="{{ route('updates') }}">
                                    {{ __("App Updates") }}
                                </x-dropdown-link>
                                <x-dropdown-link href="{{ route('maintenance') }}">
                                    {{ __("Maintenance") }}
                                </x-dropdown-link>

                                <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>
                            @endif

                            <!-- Account Management -->
                            <div class="block px-4 py-2 text-xs text-gray-400">
                                {{ __("Manage Account") }}
                            </div>
                            <x-dropdown-link href="{{ route('profile.show') }}">
                                {{ __("Profile") }}
                            </x-dropdown-link>
                            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                <x-dropdown-link href="{{ route('api-tokens.index') }}">
                                    {{ __("API Tokens") }}
                                </x-dropdown-link>
                            @endif

                            <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route("logout") }}" x-data>
                                @csrf

                                <x-dropdown-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                                    {{ __("Log Out") }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex gap-3 items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 text-[#1F2937] hover:bg-[#FCF596] focus:outline-none transition duration-150 ease-in-out border-2 border-[#1F2937]">
                    <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        @if (auth()->user()->role == 7)
            <div class="pt-2 pb-3 space-y-1">
                @foreach ([
                        "dashboard" => "Dashboard",
                        "emails" => "Emails",
                        "domains" => "Domains",
                        "pages" => "Pages",
                        "blog" => "Blog",
                        "menu" => "Menu",
                        "settings" => "Settings"
                    ]
                    as $key => $value)
                    <x-responsive-nav-link href="{{ route($key) }}" :active="request()->routeIs($key)">
                        {{ __($value) }}
                    </x-responsive-nav-link>
                @endforeach
            </div>
        @endif

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t-[3px] border-[#1F2937] bg-[#FFFBF1]">
            <div class="flex items-center px-4">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <div class="shrink-0 me-3">
                        <img class="size-10 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                    </div>
                @endif

                <div>
                    <div class="font-bold text-base text-[#1F2937]">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-[#1F2937]/70">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <!-- Account Management -->
                @if (auth()->user()->role == 7)
                    <x-responsive-nav-link href="{{ route('users') }}" :active="request()->routeIs('users')">
                        {{ __("Manage Users") }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link href="{{ route('themes') }}" :active="request()->routeIs('themes')">
                        {{ __("Themes") }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link href="{{ route('updates') }}" :active="request()->routeIs('updates')">
                        {{ __("App Updates") }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link href="{{ route('maintenance') }}" :active="request()->routeIs('maintenance')">
                        {{ __("Maintenance") }}
                    </x-responsive-nav-link>
                @endif

                <x-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                    {{ __("Profile") }}
                </x-responsive-nav-link>

                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                    <x-responsive-nav-link href="{{ route('api-tokens.index') }}" :active="request()->routeIs('api-tokens.index')">
                        {{ __("API Tokens") }}
                    </x-responsive-nav-link>
                @endif

                <!-- Authentication -->
                <form method="POST" action="{{ route("logout") }}" x-data>
                    @csrf
                    <x-responsive-nav-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                        {{ __("Log Out") }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
