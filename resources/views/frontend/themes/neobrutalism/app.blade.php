@php
    $content = [];
    if (isset($page)) {
        $content["page"] = $page;
    }
    if (isset($post)) {
        $content["post"] = $post;
    }
@endphp

<x-frontend-layout :content="$content">
    <div class="neobrutalism-theme min-h-screen flex flex-col">
        <header class="neo-header order-1 border-b-[3px] border-black">
            <div class="container mx-auto">
                <div class="flex items-center pt-8 pb-4 px-6 lg:pt-0 lg:pb-0 mb-2 lg:mb-0">
                    <a href="{{ Util::localizeRoute("home") }}">
                        @if (config("app.settings.logo") && Illuminate\Support\Facades\Storage::disk("public")->has(config("app.settings.logo")))
                            <img class="h-10 lg:h-12 w-auto" src="{{ url("storage/" . config("app.settings.logo")) }}" alt="logo" />
                        @elseif (Illuminate\Support\Facades\Storage::disk("public")->has("images/custom-logo.png"))
                            <img class="h-10 lg:h-12 w-auto" src="{{ url("storage/images/custom-logo.png") }}" alt="logo" />
                        @else
                            <div class="neo-logo relative">
                                {{ config("app.settings.name") }}
                                <div class="neo-star absolute -top-2 -right-5"></div>
                            </div>
                        @endif
                    </a>
                    <div class="flex-1">
                        @livewire("frontend.nav")
                    </div>
                </div>
                @if (config("app.settings.ads.five"))
                    <div class="flex justify-center items-center max-w-full m-4 adz-five">{!! config("app.settings.ads.five") !!}</div>
                @endif

                <div class="actions pb-4 pt-4">
                    @livewire("frontend.actions", ["in_app" => isset($page) || isset($category) || isset($post) || isset($profile) ? true : false])
                </div>
                @if (config("app.settings.ads.one"))
                    <div class="flex justify-center items-center max-w-full m-4 adz-one">{!! config("app.settings.ads.one") !!}</div>
                @endif
            </div>
        </header>

        <div class="grow container mx-auto order-2 flex flex-col md:flex-row md:space-x-2 justify-center pt-6 pb-10 px-4 md:px-6 relative z-10">
            @if (config("app.settings.ads.two"))
                <div class="flex justify-center items-center max-w-full adz-two">{!! config("app.settings.ads.two") !!}</div>
            @endif

            @if (isset($page))
                @livewire("frontend.page", ["page" => $page])
            @elseif (isset($post))
                @livewire("frontend.post", ["post" => $post])
            @elseif (isset($category))
                <main class="category flex-1 p-5">
                    <div class="neo-card p-6">
                        <h1 class="text-xl font-black uppercase">{{ __("Category") }}: {{ $category->name }}</h1>
                        @include("frontend.common.posts", ["posts" => $posts])
                    </div>
                </main>
            @elseif (isset($profile))
                @include("frontend.common.profile")
            @else
                @livewire("frontend.app")
            @endif

            @if (config("app.settings.ads.three"))
                <div class="flex justify-center items-center max-w-full adz-three">{!! config("app.settings.ads.three") !!}</div>
            @endif
        </div>

        <div id="neo-cookie-container" class="container mx-auto px-4 md:px-6 mb-6 flex justify-start order-2"></div>

        <footer class="order-3 border-t-[3px] border-black py-4 px-4 md:px-6 neo-footer">
            <div class="container mx-auto">
                <div class="flex flex-col lg:flex-row gap-3 justify-between items-center text-xs md:text-sm font-bold">
                    <div class="flex flex-wrap justify-center gap-2">
                        @foreach (\App\Models\Menu::where("status", true)->where("location", "secondary")->orderBy("order")->get() as $menu)
                            <a href="{{ Util::localizeUrl($menu->link) }}" class="hover:underline uppercase">
                                {{ $menu->name }}
                            </a>
                            @if (! $loop->last)
                                <span class="opacity-50">•</span>
                            @endif
                        @endforeach
                    </div>
                    <div class="opacity-75 text-center break-words">{{ __("Copyright") }} &copy; {{ date("Y") }} {{ config("app.settings.name") }}. {{ __("All rights reserved.") }}</div>
                </div>
            </div>
        </footer>
    </div>
</x-frontend-layout>

<style>
    /* Alpine x-cloak: hide all x-show elements before Alpine initializes.
       All neobrutalism theme tokens (.neo-*) live in resources/css/app.css. */
    [x-cloak] { display: none !important; }
</style>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const cookieBox = document.getElementById("cookie");
        const container = document.getElementById("neo-cookie-container");
        if (cookieBox && container) {
            container.appendChild(cookieBox);
        }
    });
</script>
