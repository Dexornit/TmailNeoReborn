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
    /* Alpine x-cloak: hide all x-show elements before Alpine initializes */
    [x-cloak] { display: none !important; }

    /* ═══════════════ NEOBRUTALISM CORE ═══════════════ */
    .neobrutalism-theme {
        background-color: #FDF6E3;
        font-family: var(--body-font), 'Public Sans', sans-serif;
        color: #000;
    }

    /* Override Global Cookie Policy for Neobrutalism */
    #cookie:not(.hidden) {
        display: flex !important;
        width: 100% !important;
        position: static !important;
        border: 3px solid #000 !important;
        background-color: #D7BDE2 !important;
        color: #000 !important;
        box-shadow: 4px 4px 0px #000 !important;
        padding: 10px 20px !important;
        gap: 16px !important;
        font-weight: 700 !important;
        font-size: 0.85rem !important;
        align-items: center !important;
        justify-content: space-between !important;
        margin-top: 16px !important;
    }
    #cookie_close {
        background-color: #fff !important;
        border: 2px solid #000 !important;
        box-shadow: 2px 2px 0px #000 !important;
        color: #000 !important;
        padding: 4px 12px !important;
        border-radius: 0 !important;
        font-weight: 900 !important;
        text-transform: uppercase !important;
        margin-left: auto !important;
        transition: all 0.1s ease;
        cursor: pointer;
    }
    #cookie_close:active {
        box-shadow: 0px 0px 0px #000 !important;
        transform: translate(2px, 2px);
    }

    .neo-header {
        background-color: #FDF6E3;
    }

    .neo-footer {
        background-color: #D7BDE2;
    }

    .neo-card {
        border: 3px solid #000;
        box-shadow: 5px 5px 0px #000;
        background-color: #fff;
        overflow: hidden;
    }

    .neo-btn {
        border: 3px solid #000;
        box-shadow: 3px 3px 0px #000;
        font-weight: 800;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.1s ease;
        text-transform: uppercase;
        font-size: 0.8rem;
        padding: 8px 16px;
        background-color: #fff;
    }

    .neo-btn:active {
        box-shadow: 0px 0px 0px #000;
        transform: translate(3px, 3px);
    }

    .neo-btn:hover {
        filter: brightness(0.95);
    }

    .neo-input {
        border: 3px solid #000;
        padding: 12px 16px;
        font-weight: 600;
        font-size: 0.9rem;
        background: #fff;
        outline: none;
        width: 100%;
    }

    .neo-input:focus {
        box-shadow: 3px 3px 0px #000;
    }

    .neo-input::placeholder {
        color: #aaa;
        font-weight: 400;
    }

    .neo-logo {
        font-family: 'Space Mono', 'Courier New', monospace;
        font-weight: 700;
        font-size: 1.8rem;
        line-height: 1;
    }

    .neo-star {
        width: 24px;
        height: 24px;
        background-color: #F7DC6F;
        clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);
    }

    .neo-dot-grid {
        background-image: radial-gradient(#000 1px, transparent 1px);
        background-size: 16px 16px;
    }

    .neo-zig-zag {
        width: 80px;
        height: 16px;
        background: linear-gradient(135deg, transparent 25%, #AED6F1 25%, #AED6F1 50%, transparent 50%, transparent 75%, #AED6F1 75%);
        background-size: 16px 16px;
    }

    .neo-badge {
        background-color: #F39C12;
        border: 2px solid #000;
        padding: 3px 10px;
        font-weight: 700;
        box-shadow: 2px 2px 0px #000;
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #fff;
        display: inline-block;
    }

    /* Message list */
    .neo-msg {
        border-bottom: 2px dashed #000;
        padding: 12px 20px;
        cursor: pointer;
        transition: all 0.1s ease;
    }

    .neo-msg:hover {
        background-color: #F7DC6F;
    }

    .neo-msg:last-child {
        border-bottom: none;
    }

    /* Stack effect under inbox */
    .neo-stack-1 {
        position: absolute;
        bottom: -6px;
        left: 6px;
        right: 6px;
        height: 6px;
        border-left: 3px solid #000;
        border-right: 3px solid #000;
        border-bottom: 3px solid #000;
        background: #fff;
        z-index: -1;
    }

    .neo-stack-2 {
        position: absolute;
        bottom: -12px;
        left: 12px;
        right: 12px;
        height: 6px;
        border-left: 3px solid #000;
        border-right: 3px solid #000;
        border-bottom: 3px solid #000;
        background: #fff;
        z-index: -2;
    }

    /* Override dropdown styling in neobrutalism */
    .neobrutalism-theme [x-ref="panel"] {
        border: 3px solid #000 !important;
        box-shadow: 3px 3px 0px #000 !important;
        border-radius: 0 !important;
    }

    /* Make inbox card taller */
    .neobrutalism-theme .mailbox .list .messages {
        min-height: 400px;
    }

    .neobrutalism-theme #empty-inbox,
    .neobrutalism-theme .neo-card > .flex-1 {
        min-height: 400px;
    }

    /* Footer responsive fix */
    .neo-footer {
        overflow-wrap: break-word;
        word-break: break-word;
    }
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
