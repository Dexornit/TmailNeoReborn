<nav>
    {{-- ═══════════ DESKTOP NAV ═══════════ --}}
    <div class="px-5 hidden lg:flex sticky top-0 z-40 h-20">
        <div class="w-full my-auto">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <div class="flex items-center space-x-4">
                        @foreach ($menus as $menu)
                            @if ($menu->hasChild())
                                <div @click.away="open = false" class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" class="flex flex-row items-center w-full px-3 py-2 text-sm font-bold text-left bg-transparent md:w-auto md:inline hover:underline underline-offset-4 uppercase">
                                        <span>{!! __($menu->name) !!}</span>
                                        <svg fill="currentColor" viewBox="0 0 20 20" :class="{'rotate-180': open, 'rotate-0': !open}" class="inline w-4 h-4 mt-1 ml-1 transition-transform duration-200 transform md:-mt-1"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                    </button>
                                    <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute left-0 w-full mt-2 origin-top-right md:w-48 z-50">
                                        <div class="neo-card bg-white">
                                            @foreach ($menu->getChild() as $child)
                                                <a class="block px-4 py-2 text-sm font-bold hover:bg-yellow-200 border-b-2 border-black last:border-b-0" href="{{ Util::localizeUrl($child->link) }}" target="{{ $child->target }}">{!! __($child->name) !!}</a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @else
                                @if ($menu->parent_id === null)
                                    <a href="{{ Util::localizeUrl($menu->link) }}" class="px-3 py-2 text-sm font-bold text-left uppercase hover:underline underline-offset-4 {{ url()->current() === Util::localizeUrl($menu->link) ? "underline" : "" }}" target="{{ $menu->target }}">{!! __($menu->name) !!}</a>
                                @endif
                            @endif
                        @endforeach

                        @if (count(config("app.settings.socials", [])) > 0)
                            <div class="flex items-center gap-4 px-2">
                                @foreach (config("app.settings.socials", []) as $social)
                                    <a href="{{ $social["link"] }}" target="_blank" class="text-xl hover:scale-110 transition-transform" rel="noopener noreferrer">
                                        <i class="{{ $social["icon"] }}"></i>
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        @if (Auth::check() && Auth::user()->role == 7)
                            <a href="{{ route("admin") }}" class="neo-btn neo-btn--danger py-1 px-3 text-xs">{{ __("Admin") }}</a>
                        @endif

                        @if (! Auth::check())
                            <a href="{{ route("login") }}" class="neo-btn neo-btn--success py-1 px-3 text-xs">{{ __("Login") }}</a>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @if (auth()->check())
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                    <button class="flex text-sm border-[3px] border-black rounded-full">
                                        <img class="size-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                                    </button>
                                @else
                                    <span class="inline-flex">
                                        <button type="button" class="neo-btn py-1 px-3 text-xs bg-white">
                                            {{ Auth::user()->name }}
                                            <svg class="ms-1 size-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                        </button>
                                    </span>
                                @endif
                            </x-slot>
                            <x-slot name="content">
                                <div class="block px-4 py-2 text-xs font-bold uppercase opacity-50">{{ __("Manage Account") }}</div>
                                <x-dropdown-link href="{{ route('profile') }}">{{ __("Profile") }}</x-dropdown-link>
                                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                    <x-dropdown-link href="{{ route('api-tokens.index') }}">{{ __("API Tokens") }}</x-dropdown-link>
                                @endif
                                <div class="border-t-2 border-black my-1"></div>
                                <form method="POST" action="{{ route("logout") }}" x-data>
                                    @csrf
                                    <x-dropdown-link href="{{ route('logout') }}" @click.prevent="$root.submit();">{{ __("Log Out") }}</x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    @endif

                    <div class="relative">
                        <form action="{{ route("locale") }}" id="locale-form" method="post">
                            @csrf
                            <select class="neo-btn py-1 px-3 text-xs bg-white cursor-pointer" name="locale" id="locale" x-on:change="$el.form.submit()">
                                @foreach (config("app.settings.languages") as $code => $language)
                                    @if ($language["is_active"])
                                        <option {{ app()->getLocale() == $code ? "selected" : "" }} value="{{ $code }}">{{ $language["label"] }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════ MOBILE NAV ═══════════ --}}
    <div class="lg:hidden" x-data="{ open: false }">
        <div class="flex items-center gap-3 absolute top-8 right-6">
            @if (auth()->check())
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                            <button class="flex text-sm border-[3px] border-black rounded-full">
                                <img class="size-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                            </button>
                        @else
                            <span class="inline-flex">
                                <button type="button" class="neo-btn py-1 px-3 text-xs bg-white">
                                    {{ Auth::user()->name }}
                                    <svg class="ms-1 size-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                </button>
                            </span>
                        @endif
                    </x-slot>
                    <x-slot name="content">
                        <div class="block px-4 py-2 text-xs font-bold uppercase opacity-50">{{ __("Manage Account") }}</div>
                        <x-dropdown-link href="{{ route('profile') }}">{{ __("Profile") }}</x-dropdown-link>
                        @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                            <x-dropdown-link href="{{ route('api-tokens.index') }}">{{ __("API Tokens") }}</x-dropdown-link>
                        @endif
                        <div class="border-t-2 border-black my-1"></div>
                        <form method="POST" action="{{ route("logout") }}" x-data>
                            @csrf
                            <x-dropdown-link href="{{ route('logout') }}" @click.prevent="$root.submit();">{{ __("Log Out") }}</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            @endif

            <div @click="open = true" class="w-8 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
            </div>
        </div>
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click.away="open = false" class="neo-mobile-menu fixed inset-0 min-h-screen w-full z-50">
            <div @click="open = false" class="absolute top-6 right-6 w-8 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <div class="w-full mx-auto mt-20">
                <div class="flex flex-col items-center justify-between">
                    <div class="flex flex-col items-center space-y-3">
                        @foreach ($menus as $menu)
                            @if ($menu->hasChild())
                                <div @click.away="childOpen = false" class="relative" x-data="{ childOpen: false }">
                                    <button @click="childOpen = !childOpen" class="neo-btn py-2 px-5 bg-white text-sm">
                                        <span>{!! __($menu->name) !!}</span>
                                        <svg fill="currentColor" viewBox="0 0 20 20" :class="{'rotate-180': childOpen, 'rotate-0': !childOpen}" class="inline w-4 h-4 transition-transform duration-200">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                    <div x-show="childOpen" x-transition class="mt-2 z-10">
                                        <div class="neo-card bg-white">
                                            @foreach ($menu->getChild() as $child)
                                                <a class="block px-4 py-2 text-sm font-bold text-center hover:bg-yellow-200 border-b-2 border-black last:border-b-0" href="{{ Util::localizeUrl($child->link) }}" target="{{ $child->target }}">{!! __($child->name) !!}</a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @else
                                @if ($menu->parent_id === null)
                                    <a href="{{ Util::localizeUrl($menu->link) }}" class="neo-btn py-2 px-5 bg-white text-sm" target="{{ $menu->target }}">{!! __($menu->name) !!}</a>
                                @endif
                            @endif
                        @endforeach

                        @if (Auth::check() && Auth::user()->role == 7)
                            <a href="{{ route("admin") }}" target="_blank" class="neo-btn neo-btn--danger py-2 px-5 text-sm">{{ __("Admin") }}</a>
                        @endif

                        @if (! Auth::check())
                            <a href="{{ route("login") }}" class="neo-btn neo-btn--success py-2 px-5 text-sm">{{ __("Login") }}</a>
                        @endif
                    </div>
                    <div class="flex flex-col items-center space-y-3 mt-8">
                        <div class="flex flex-wrap justify-center gap-4">
                            @foreach (config("app.settings.socials", []) as $social)
                                <a href="{{ $social["link"] }}" target="_blank" class="neo-btn neo-btn--social p-3 flex items-center justify-center bg-white hover:scale-105 transition-transform" rel="noopener noreferrer">
                                    <i class="{{ $social["icon"] }}"></i>
                                </a>
                            @endforeach
                        </div>
                        <div class="relative">
                            <form action="{{ route("locale", "") }}" id="locale-form-mobile" method="post">
                                @csrf
                                <select class="neo-btn py-1 px-3 text-xs bg-white cursor-pointer" name="locale" id="locale-mobile" x-on:change="$el.form.submit()">
                                    @foreach (config("app.settings.languages") as $code => $language)
                                        @if ($language["is_active"])
                                            <option {{ app()->getLocale() == $code ? "selected" : "" }} value="{{ $code }}">{{ $language["label"] }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
