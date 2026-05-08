<div>
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-6">
        <x-card class="neo-stat neo-stat--sage px-6 py-4 col-span-2 md:col-span-4 lg:col-span-2">
            <div class="text-4xl mb-5"><i class="hgi hgi-stroke hgi-mail-receive-02"></i></div>
            <div class="ml-1 text-2xl font-extrabold">{{ $messagesReceived }}</div>
            <div class="ml-1 text-xs font-bold uppercase tracking-wide opacity-80">{{ __("Messages Received") }}</div>
        </x-card>
        <x-card class="neo-stat neo-stat--blue px-6 py-4 col-span-1 lg:col-span-2">
            <div class="text-4xl mb-5"><i class="hgi hgi-stroke hgi-mail-at-sign-01"></i></div>
            <div class="ml-1 text-2xl font-extrabold">{{ $emailsCreated }}</div>
            <div class="ml-1 text-xs font-bold uppercase tracking-wide opacity-80">{{ __("Emails Generated") }}</div>
        </x-card>
        <x-card class="neo-stat neo-stat--yellow px-6 py-4">
            <div class="text-4xl mb-5"><i class="hgi hgi-stroke hgi-license"></i></div>
            <div class="ml-1 text-2xl font-extrabold">{{ $pagesCreated }}</div>
            <div class="ml-1 text-xs font-bold uppercase tracking-wide opacity-80">{{ __("Pages") }}</div>
        </x-card>
        <x-card class="neo-stat neo-stat--red px-6 py-4">
            <div class="text-4xl mb-5"><i class="hgi hgi-stroke hgi-pencil-edit-02"></i></div>
            <div class="ml-1 text-2xl font-extrabold">{{ $blogPostsCreated }}</div>
            <div class="ml-1 text-xs font-bold uppercase tracking-wide opacity-80">{{ __("Post") }}</div>
        </x-card>
        <x-card class="neo-stat neo-stat--cream px-6 py-4">
            <div class="text-4xl mb-5"><i class="hgi hgi-stroke hgi-user-multiple-02"></i></div>
            <div class="ml-1 text-2xl font-extrabold">{{ $usersRegistered }}</div>
            <div class="ml-1 text-xs font-bold uppercase tracking-wide opacity-80">{{ __("Users") }}</div>
        </x-card>
    </div>
</div>
