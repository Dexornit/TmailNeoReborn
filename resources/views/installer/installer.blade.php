<div class="grid grid-cols-6 gap-6">
    <div wire:loading wire:target="save">
        <div class="flex items-center w-full h-full fixed top-0 left-0 bg-white dark:bg-gray-900 opacity-75 z-50">
            <div class="text-3xl mx-auto">
                <i class="hgi hgi-stroke hgi-loading-03 hgi-spin"></i>
            </div>
        </div>
    </div>
    @if ($success)
        <div class="col-span-6">
            <div class="w-full py-3 px-4 overflow-hidden rounded-md flex items-center border bg-green-50 dark:bg-green-900 border-green-500">
                <div class="text-green-500 w-10">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="ml-4 flex-1">
                    <div class="text-lg text-gray-600 dark:text-gray-50 font-semibold">{{ __("Success") }}</div>
                    <div class="text-sm">{{ $success }}</div>
                </div>
            </div>
        </div>
    @endif

    @if ($error)
        <div class="col-span-6">
            <div class="w-full py-3 px-4 overflow-hidden rounded-md flex items-center border bg-red-50 dark:bg-red-900 border-red-500">
                <div class="text-red-500 w-10">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4 flex-1">
                    <div class="text-lg text-gray-600 dark:text-gray-50 font-semibold">{{ __("Error") }}</div>
                    <div class="text-sm">{{ $error }}</div>
                </div>
            </div>
        </div>
    @endif

    @if ($current === 0)
        <h3 class="col-span-6 text-xl pt-2">{{ __("Database Details") }}</h3>
        <div class="col-span-6">
            <x-label for="hostname" value="{{ __('Hostname') }}" />
            <x-input id="hostname" type="text" class="mt-1 block w-full" placeholder="{{ __('eg. localhost') }}" wire:model="state.db.host" />
            <x-input-error for="state.db.host" class="mt-2" />
        </div>
        <div class="col-span-6">
            <x-label for="port" value="{{ __('Port') }}" />
            <x-input id="port" type="text" class="mt-1 block w-full" placeholder="{{ __('eg. 3306') }}" wire:model="state.db.port" />
            <x-input-error for="state.db.port" class="mt-2" />
        </div>
        <div class="col-span-6">
            <x-label for="connection" value="{{ __('Connection') }}" />
            <x-input id="connection" type="text" class="mt-1 block w-full" placeholder="{{ __('eg. mysql') }}" wire:model="state.db.connection" />
            <x-input-error for="state.db.connection" class="mt-2" />
        </div>
        <div class="col-span-6">
            <x-label for="db_name" value="{{ __('Database') }}" />
            <x-input id="db_name" type="text" class="mt-1 block w-full" placeholder="{{ __('eg. db_name') }}" wire:model="state.db.database" />
            <x-input-error for="state.db.database" class="mt-2" />
        </div>
        <div class="col-span-6">
            <x-label for="db_username" value="{{ __('Username') }}" />
            <x-input id="db_username" type="text" class="mt-1 block w-full" placeholder="{{ __('eg. db_user') }}" wire:model="state.db.username" />
            <x-input-error for="state.db.username" class="mt-2" />
        </div>
        <div x-data="{ show: false }" class="col-span-6">
            <x-label for="db_password" value="{{ __('Password') }}" />
            <div class="relative">
                <x-input id="db_password" x-bind:type="show ? 'text' : 'password'" class="mt-1 block w-full" wire:model="state.db.password" placeholder="{{ __('••••••') }}" autocomplete="new-password" />
                <div x-on:click="show = !show" x-text="show ? 'HIDE' : 'SHOW'" class="cursor-pointer absolute inset-y-0 right-0 flex items-center px-5 text-xs"></div>
            </div>
            <x-input-error for="state.db.password" class="mt-2" />
        </div>
    @elseif ($current === 1)
        <div class="col-span-6">
            <x-label for="app_name" value="{{ __('App Name') }}" />
            <x-input id="app_name" type="text" class="mt-1 block w-full" placeholder="{{ __('eg. TMail') }}" wire:model="state.app_name" />
            <x-input-error for="state.app_name" class="mt-2" />
        </div>
        <div class="col-span-6">
            <x-label for="license_key" value="{{ __('License Key') }}" />
            <x-input id="license_key" type="text" class="mt-1 block w-full" placeholder="{{ __('eg. 88d7f6fb-5bbc-4f86-a05b-d6b24183b25f') }}" wire:model="state.license_key" />
            <small>{{ __("License Key is the Purchase Code that you must have received from CodeCanyon after purchasing TMail") }}</small>
            <x-input-error for="state.license_key" class="mt-2" />
        </div>
    @elseif ($current === 2)
        <div class="col-span-6">
            <x-label value="{{ __('Domains') }}" />
            @foreach ($state["domains"] as $key => $domains)
                <div class="flex gap-3 mt-2">
                    <x-input type="text" placeholder="{{ __('eg. google.com') }}" class="block w-full" wire:model="state.domains.{{ $key }}" />
                    <x-danger-button wire:click="remove('domains', {{ $key }})"><i class="hgi hgi-stroke hgi-delete-02"></i></x-danger-button>
                </div>
                <x-input-error for="state.domains.{{ $key }}" class="mt-1 mb-2" />
            @endforeach

            @if (count($state["domains"]) == 0)
                <x-input-error for="state.domains.0" class="mt-1 mb-2" />
            @endif

            <x-secondary-button class="mt-2" wire:click="add('domains')">Add</x-secondary-button>
        </div>
        <h3 class="col-span-6 text-xl pt-2">{{ __("Engine") }}</h3>
        <div class="col-span-6">
            <x-label for="engine" value="{{ __('Engine') }}" />
            <div class="relative">
                <x-select class="block w-full" wire:model.live="state.engine">
                    <option value="" disabled>{{ __("Select Engine") }}</option>
                    <option value="delivery">{{ __("TMail Delivery") }}</option>
                    <option value="imap">{{ __("IMAP") }}</option>
                </x-select>
            </div>
            <x-input-error for="state.engine" class="mt-2" />
        </div>

        @if ($state["engine"] == "delivery")
            <div class="col-span-6 -mt-3">
                <p class="text-gray-600 text-sm">{{ __("You can continue the TMail Delivery setup later by logging into Admin Panel") }}</p>
            </div>
        @elseif ($state["engine"] == "imap")
            <h3 class="col-span-6 text-xl pt-2">{{ __("IMAP Details") }}</h3>
            <div class="col-span-6">
                <x-label for="hostname" value="{{ __('Hostname') }}" />
                <x-input id="hostname" type="text" class="mt-1 block w-full" placeholder="{{ __('eg. mail.johndoe.com') }}" wire:model="state.imap.host" />
                <x-input-error for="state.imap.host" class="mt-2" />
            </div>
            <div class="col-span-6">
                <x-label for="port" value="{{ __('Port') }}" />
                <x-input id="port" type="text" class="mt-1 block w-full" placeholder="{{ __('eg. 993') }}" wire:model="state.imap.port" />
                <x-input-error for="state.imap.port" class="mt-2" />
            </div>
            <div class="col-span-6">
                <x-label for="encryption" value="{{ __('Encryption') }}" />
                <div class="relative">
                    <x-select class="block w-full" wire:model.live="state.imap.encryption">
                        <option value="">{{ __("None") }}</option>
                        <option value="ssl">{{ __("SSL") }}</option>
                        <option value="tls">{{ __("TLS") }}</option>
                    </x-select>
                </div>
                <x-input-error for="state.imap.encryption" class="mt-2" />
            </div>
            <div class="col-span-6">
                <label for="validate_cert" class="flex items-center cursor-pointer">
                    <x-label class="mr-4" value="{{ __('Validate Encryption Certificate') }}" />
                    <div class="relative">
                        <input id="validate_cert" type="checkbox" class="hidden" wire:model="state.imap.validate_cert" />
                        <div class="toggle-path bg-gray-200 w-9 h-5 rounded-full shadow-inner"></div>
                        <div class="toggle-circle absolute w-3.5 h-3.5 bg-white rounded-full shadow inset-y-0 left-0"></div>
                    </div>
                </label>
            </div>
            <div class="col-span-6">
                <x-label for="username" value="{{ __('Username') }}" />
                <x-input id="username" type="text" class="mt-1 block w-full" placeholder="{{ __('eg. username') }}" wire:model="state.imap.username" />
                <x-input-error for="state.imap.username" class="mt-2" />
            </div>
            <div x-data="{ show: false }" class="col-span-6">
                <x-label for="password" value="{{ __('Password') }}" />
                <div class="relative">
                    <x-input id="password" x-bind:type="show ? 'text' : 'password'" class="mt-1 block w-full" placeholder="{{ __('••••••') }}" wire:model="state.imap.password" autocomplete="new-password" />
                    <div x-on:click="show = !show" x-text="show ? 'HIDE' : 'SHOW'" class="cursor-pointer absolute inset-y-0 right-0 flex items-center px-5 text-xs"></div>
                </div>
                <x-input-error for="state.imap.password" class="mt-2" />
            </div>
            <div x-data="{ show_advance: false }" class="col-span-6">
                <label for="show_advance" class="flex items-center cursor-pointer">
                    <x-label class="mr-4" value="{{ __('Show Advance') }}" />
                    <div class="relative">
                        <input x-model="show_advance" id="show_advance" type="checkbox" class="hidden" />
                        <div class="toggle-path bg-gray-200 w-9 h-5 rounded-full shadow-inner"></div>
                        <div class="toggle-circle absolute w-3.5 h-3.5 bg-white rounded-full shadow inset-y-0 left-0"></div>
                    </div>
                </label>
                <div x-show="show_advance" class="mt-6">
                    <x-label for="default_account" value="{{ __('Default Account') }}" />
                    <x-input id="default_account" type="text" class="mt-1 block w-full" wire:model="state.imap.default_account" />
                </div>
                <div x-show="show_advance" class="mt-6">
                    <x-label for="protocol" value="{{ __('Protocol') }}" />
                    <x-input id="protocol" type="text" class="mt-1 block w-full" wire:model="state.imap.protocol" />
                </div>
            </div>
        @endif
    @elseif ($current === 3)
        <h3 class="col-span-6 text-xl pt-2">{{ __("Admin Account") }}</h3>
        <div class="col-span-6">
            <x-label for="name" value="{{ __('Name') }}" />
            <x-input id="name" type="text" class="mt-1 block w-full" placeholder="{{ __('eg. John') }}" wire:model="state.admin.name" />
            <x-input-error for="state.admin.name" class="mt-2" />
        </div>
        <div class="col-span-6">
            <x-label for="email" value="{{ __('Email ID') }}" />
            <x-input id="email" type="text" class="mt-1 block w-full" placeholder="{{ __('eg. john@doe.com') }}" wire:model="state.admin.email" />
            <x-input-error for="state.admin.email" class="mt-2" />
        </div>
        <div x-data="{ show_admin_password: false }" class="col-span-6">
            <x-label for="admin_password" value="{{ __('Password') }}" />
            <div class="relative">
                <x-input id="admin_password" x-bind:type="show_admin_password ? 'text' : 'password'" class="mt-1 block w-full" placeholder="{{ __('••••••') }}" wire:model="state.admin.password" autocomplete="new-password" />
                <div x-on:click="show_admin_password = !show_admin_password" x-text="show_admin_password ? 'HIDE' : 'SHOW'" class="cursor-pointer absolute inset-y-0 right-0 flex items-center px-5 text-xs"></div>
            </div>
            <x-input-error for="state.admin.password" class="mt-2" />
        </div>
    @endif

    @if ($current === 4)
        <div class="col-span-6">
            <a class="w-full block text-center bg-indigo-700 form-input rounded-md text-white border-0 text-sm" href="{{ route("login") }}">
                <span class="mr-3 font-bold">{{ __("Visit TMail - Admin Panel") }}</span>
                <i class="hgi hgi-stroke hgi-arrow-right-02"></i>
            </a>
        </div>
    @else
        <div class="col-span-6">
            <button wire:click="save" class="w-full bg-indigo-700 form-input rounded-md text-white border-0 text-sm">
                <span class="mr-3 font-bold">{{ __("Save & Next") }}</span>
                <i class="hgi hgi-stroke hgi-arrow-right-02"></i>
            </button>
        </div>
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (sessionStorage.getItem('migrations')) {
                console.log('Running migrations...');
                setTimeout(function () {
                    if (typeof Livewire !== 'undefined') {
                        Livewire.dispatch('runMigrations');
                    } else {
                        console.error('Livewire not available');
                    }
                }, 100);
                sessionStorage.removeItem('migrations');
            }
        });

        window.addEventListener('run-migrations', function () {
            console.log('Migration event received, reloading page...');
            sessionStorage.setItem('migrations', true);
            location.reload();
        });
    </script>
</div>
