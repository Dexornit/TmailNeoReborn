<x-form-section submit="save">
    <x-slot name="title">
        {{ __("Export / Import") }}
    </x-slot>

    <x-slot name="description">
        {{ __("You can export all the TMail settings using this. You can use this to restore the site settings if you plan to re-create the site from scatrch.") }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6">
            <x-label value="{{ __('Export Settings') }}" />
            <x-button class="mt-2" type="button" wire:click="export">{{ __("Export") }}</x-button>
        </div>
        <div class="col-span-6">
            <x-label value="{{ __('Import Settings') }}" />
            <small class="text-red-700 font-bold block mt-1">{{ __("Caution: This will override all the existing settings!") }}</small>
            <input class="block mt-2 text-xs" type="file" name="import" id="import" />
            <x-button id="import-settings" class="mt-2" type="button">{{ __("Import") }}</x-button>
        </div>
        <x-action-message class="mr-3" on="imported">
            {{ __("Imported.") }}
        </x-action-message>
    </x-slot>
</x-form-section>

@script
    <script>
        $wire.on('settingsExportFetched', (settings) => {
            let content = 'data:text/plain;charset=utf-8,' + btoa(settings);
            let link = document.createElement('a');
            link.setAttribute('href', content);
            link.setAttribute('download', 'settings.tmail');
            document.body.appendChild(link);
            link.click();
            link.remove();
        });
        if (document.getElementById('import-settings')) {
            document.getElementById('import-settings').addEventListener('click', () => {
                let reader = new FileReader();
                reader.onload = (event) => {
                    let settings = atob(event.target.result);
                    if (settings) {
                        Livewire.dispatch('upload', {
                            settings,
                        });
                    }
                };
                reader.readAsText(document.querySelector('#import[type=file]').files[0]);
            });
            window.addEventListener('refresh', () => {
                setTimeout(() => {
                    window.scrollTo(0, 0);
                    location.reload();
                }, 1000);
            });
        }
    </script>
@endscript
