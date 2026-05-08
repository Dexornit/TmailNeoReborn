<main class="flex-1" x-data="{ show: false, id: 0 }">
    @if ($error)
        <div id="imap-error" class="flex items-center w-full h-full fixed top-0 left-0 z-50" style="background-color: rgba(241,148,138,0.95);">
            <div class="flex flex-col mx-auto text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-24 h-24 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-3xl font-black uppercase my-3">{{ __("IMAP Broken") }}</div>
                <div class="neo-badge inline-block mx-auto text-sm">{{ $error }}</div>
            </div>
        </div>
    @endif

    {{-- Single outer card — always rendered, no DOM morph between states --}}
    <div class="text-sm relative">
        <div class="mailbox neo-card" wire:key="mailbox-card">

            {{-- Message List View — always rendered in DOM, hidden with CSS when viewing single message --}}
            <div x-show="!show" style="display: block;" class="list w-full">
                {{-- Header row always visible --}}
                <div class="flex items-center py-4 px-5 border-b-[3px] border-black font-black text-xs uppercase w-full" style="background-color: #F7DC6F;">
                    <div class="w-1/2 md:w-3/12 flex-shrink-0">{{ __("Sender") }}</div>
                    <div class="w-1/2 md:w-7/12 flex-shrink-0">{{ __("Subject") }}</div>
                    <div class="hidden md:flex md:w-2/12 flex-shrink-0 justify-end">{{ __("Time") }}</div>
                </div>

                {{-- Messages or empty/loading state --}}
                <div class="messages-container flex flex-col flex-1" style="min-height: 320px;">
                    @if ($messages && count($messages) > 0)
                        <div class="messages flex flex-col justify-start">
                            @foreach ($messages as $i => $message)
                                @if ($i % 3 == 0 && config("app.settings.ads.four"))
                                    <div class="adz-four">{!! config("app.settings.ads.four") !!}</div>
                                @endif

                                @if (! in_array($i, $deleted))
                                    <div x-on:click="
                                        show = true
                                        id = {{ $message['id'] }}
                                        document.querySelector('button.delete').setAttribute('wire:click', 'delete({{ $message['id'] }})')
                                    " class="neo-msg flex items-center w-full" wire:key="msg-{{ $message['id'] }}" data-id="{{ $message['id'] }}">
                                        <div class="w-1/2 md:w-3/12 flex-shrink-0 pr-2 overflow-hidden">
                                            <div class="font-bold truncate">{{ $message['sender_name'] }}</div>
                                            <div class="text-xs opacity-50 truncate">{{ $message['sender_email'] }}</div>
                                        </div>
                                        <div class="w-1/2 md:w-7/12 flex-shrink-0 truncate">{{ $message['subject'] }}</div>
                                        <div class="hidden md:block md:w-2/12 flex-shrink-0">
                                            <div class="flex justify-end opacity-60 text-xs font-bold whitespace-nowrap">{{ $message['datediff'] }}</div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="flex-1 flex flex-col justify-center items-center min-h-[320px] py-16 opacity-50">
                            @if ($initial)
                                <div class="neo-star w-12 h-12 mb-4"></div>
                                <div class="text-lg font-black uppercase">{{ __("Empty Inbox") }}</div>
                            @else
                                <i class="fas fa-circle-notch fa-spin-fast text-2xl opacity-30"></i>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Single Message View --}}
            <div x-show="show" style="display: none;" class="message">
                <div class="flex items-center py-4 px-5 border-b-[3px] border-black font-bold text-sm" style="background-color: #AED6F1;">
                    <div class="w-full flex justify-between items-center">
                        <div x-on:click="show = false" class="flex items-center cursor-pointer hover:opacity-70">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            <span class="ml-1 uppercase text-xs font-black">{{ __("Go Back to Inbox") }}</span>
                        </div>
                        <div class="flex gap-2">
                            <a class="download neo-btn py-1 px-3 text-xs bg-white" href="#" x-bind:data-id="id">{{ __("Download") }}</a>
                            <button x-on:click="
                                id = 0
                                show = false
                            " class="delete neo-btn py-1 px-3 text-xs" style="background-color: #F1948A;" wire:click="delete(1)">{{ __("Delete") }}</button>
                        </div>
                    </div>
                </div>
                @foreach ($messages as $message)
                    <div x-show="id === {{ $message['id'] }}" style="display: none;" id="message-{{ $message['id'] }}" wire:key="msg-detail-{{ $message['id'] }}" class="message-detail">
                        <textarea class="hidden">To: {{ $this->email }}&#13;From: "{{ $message['sender_name'] }}" <{{ $message['sender_email'] }}>&#13;Subject: {{ $message['subject'] }}&#13;Date: {{ $message['date'] }}&#13;Content-Type: text/html&#13;&#13;{{ $message['content'] }}</textarea>
                        <div class="flex justify-between items-center py-4 px-5">
                            <div>
                                <div class="font-bold">{{ $message['sender_name'] }}</div>
                                <div class="text-xs opacity-50">{{ $message['sender_email'] }}</div>
                            </div>
                            <div>
                                <div class="text-xs font-bold opacity-50">{{ __("Date") }}</div>
                                <div class="text-xs opacity-50">{{ $message['date'] }}</div>
                            </div>
                        </div>
                        <div class="border-t border-b border-dashed border-gray-300 py-3 px-5 font-bold">
                            {{ $message['subject'] }}
                        </div>
                        <div class="py-4 px-5 bg-white">
                            <iframe class="w-full min-h-[600px]" srcdoc="{{ $message['content'] }}" frameborder="0"></iframe>
                            @if (count($message['attachments']) > 0)
                                <div class="border-t-2 border-dashed border-gray-200 mt-4 pt-4">
                                    <span class="text-xs font-black uppercase opacity-60">{{ __("Attachments") }}</span>
                                    <div class="flex flex-wrap gap-2 mt-2">
                                        @foreach ($message['attachments'] as $attachment)
                                            <a class="neo-btn py-1 px-3 text-xs bg-white" href="{{ $attachment['url'] }}" download>
                                                <i class="fas fa-chevron-circle-down"></i>
                                                <span>{{ $attachment['file'] }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

        </div>

        {{-- Stack effect --}}
        <div class="neo-stack-1"></div>
        <div class="neo-stack-2"></div>
    </div>
</main>
