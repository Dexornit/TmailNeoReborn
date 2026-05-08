<main class="flex-1 page px-7 py-5">
    <div class="neo-card p-6 md:p-8">
        @if (isset($isBlog) && $isBlog)
            {!! $content[0] !!}
            @if ($posts && $posts->count())
                @include("frontend.common.posts", ["posts" => $posts])
            @endif

            @if (isset($content[1]))
                {!! $content[1] !!}
            @endif
        @else
            {!! $page->content !!}
        @endif
    </div>
</main>
