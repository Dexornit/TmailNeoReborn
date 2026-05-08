@props(['style' => session('flash.bannerStyle', 'success'), 'message' => session('flash.banner')])

<div x-data="{{ json_encode(['show' => true, 'style' => $style, 'message' => $message]) }}"
        :class="{
            'bg-[#CADCAE] border-b-[3px] border-[#1F2937] text-[#1F2937]': style == 'success',
            'bg-[#EB4C4C] border-b-[3px] border-[#1F2937] text-white': style == 'danger',
            'bg-[#FCF596] border-b-[3px] border-[#1F2937] text-[#1F2937]': style == 'warning',
            'bg-[#80C4E9] border-b-[3px] border-[#1F2937] text-[#1F2937]': style != 'success' && style != 'danger' && style != 'warning'
        }"
        style="display: none;"
        x-show="show && message"
        x-on:banner-message.window="
            style = event.detail.style;
            message = event.detail.message;
            show = true;
        ">
    <div class="max-w-screen-xl mx-auto py-2 px-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between flex-wrap">
            <div class="w-0 flex-1 flex items-center min-w-0">
                <span class="flex p-2 border-2 border-[#1F2937]"
                    :class="{
                        'bg-[#CADCAE]': style == 'success',
                        'bg-[#EB4C4C]': style == 'danger',
                        'bg-[#FCF596]': style == 'warning',
                        'bg-[#80C4E9]': style != 'success' && style != 'danger' && style != 'warning'
                    }">
                    <svg x-show="style == 'success'" class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg x-show="style == 'danger'" class="size-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <svg x-show="style != 'success' && style != 'danger' && style != 'warning'" class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    <svg x-show="style == 'warning'" class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5" fill="none" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4v.01" />
                    </svg>
                </span>

                <p class="ms-3 font-bold text-sm truncate" x-text="message"></p>
            </div>

            <div class="shrink-0 sm:ms-3">
                <button
                    type="button"
                    class="-me-1 flex p-2 border-2 border-[#1F2937] hover:bg-black/10 focus:outline-none sm:-me-2 transition"
                    aria-label="Dismiss"
                    x-on:click="show = false">
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
