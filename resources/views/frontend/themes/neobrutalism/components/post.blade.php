<main class="post flex-1 p-5">
    <div class="neo-card overflow-hidden mb-6">
        <div class="relative h-72 md:h-96 bg-cover bg-center bg-no-repeat" style="background-image: url('{{ $post->image }}')">
            <div class="absolute inset-0 bg-black bg-opacity-40"></div>
            <div class="relative z-10 flex flex-col justify-end h-full p-6 md:p-8">
                <div class="flex flex-wrap gap-2 mb-3">
                    @foreach ($post->categories as $category)
                        <a href="{{ Util::localizeUrl(route("blog.category", $category->slug)) }}" class="neo-badge">{{ $category->name }}</a>
                    @endforeach
                </div>
                <h1 class="text-2xl md:text-3xl font-black text-white uppercase leading-tight max-w-4xl">{{ $post->title }}</h1>
                <p class="text-white opacity-75 font-bold mt-2 text-sm">{{ $post->created_at->format("F j, Y") }}</p>
            </div>
        </div>
    </div>

    <div class="neo-card p-6 md:p-8">
        <article class="prose max-w-none">
            {!! $post->content !!}
        </article>
    </div>

    @if (config("app.settings.disqus.enable"))
        <div class="neo-card p-6 md:p-8 mt-6">
            <div id="disqus_thread"></div>
            <script>
                (function () {
                    var d = document, s = d.createElement('script');
                    s.src = 'https://{{ config("app.settings.disqus.shortname") }}.disqus.com/embed.js';
                    s.setAttribute('data-timestamp', +new Date());
                    (d.head || d.body).appendChild(s);
                })();
            </script>
        </div>
    @endif
</main>
