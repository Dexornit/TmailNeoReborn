<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use App\Models\Menu;

class Nav extends Component {

    public $menus, $current_route;

    public function mount() {
        $this->menus = Menu::where('status', true)->where('location', 'primary')->where('parent_id', null)->orderBy('order')->get();
    }

    public function render() {
        return view('frontend.themes.' . config('app.settings.theme') . '.components.nav');
    }
}
