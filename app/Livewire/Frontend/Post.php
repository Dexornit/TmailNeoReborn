<?php

namespace App\Livewire\Frontend;

use Livewire\Component;

class Post extends Component {

    public $post;

    public function mount($post = null) {
        $this->post = $post;
    }

    public function render() {
        return view('frontend.themes.' . config('app.settings.theme') . '.components.post');
    }
}
