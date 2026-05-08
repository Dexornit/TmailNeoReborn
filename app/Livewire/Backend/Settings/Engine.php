<?php

namespace App\Livewire\Backend\Settings;

use Livewire\Component;
use App\Models\Setting;

class Engine extends Component {
    /**
     * Components State
     */
    public $state = [
        'engine' => 'imap',
        'delivery' => [
            'key' => '',
        ]
    ];

    public function updatedState($value) {
        $this->dispatch('engineUpdated', $value);
    }

    public function mount() {
        $this->state['engine'] = config('app.settings.engine');
        $this->state['delivery'] = config('app.settings.delivery');
        $this->state['delivery']['key'] = base64_encode(config('app.url') . '|' . $this->state['delivery']['key']);
    }

    public function save() {
        $setting = Setting::where('key', 'engine')->first();
        $setting->value = serialize($this->state[$setting->key]);
        $setting->save();
        $this->dispatch('saved');
    }

    public function render() {
        return view('backend.settings.engine');
    }
}
