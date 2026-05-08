@props(["id" => null, "maxWidth" => null])

<x-modal :id="$id" :maxWidth="$maxWidth" {{ $attributes->merge(['class' => 'neo-modal-surface']) }}>
    <div class="px-6 py-4">
        <div class="text-lg font-black uppercase py-2 tracking-wide" style="color: var(--neo-text);">
            {{ $title }}
        </div>

        <div class="mt-4 text-sm max-h-[60vh] overflow-y-auto scrollbar-thin scrollbar-thumb-transparent scrollbar-track-transparent px-px" style="color: var(--neo-text); scrollbar-width: none; -ms-overflow-style: none">
            {{ $content }}
        </div>
    </div>

    <div class="flex flex-row justify-end px-6 py-4 border-t-[3px] border-black text-end" style="background-color: var(--neo-accent-4);">
        {{ $footer }}
    </div>
</x-modal>
