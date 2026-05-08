<?php

namespace App\Livewire\Frontend;

use App\Models\Post;
use App\Services\Util;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Page extends Component {

    public $page, $isBlog = false;

    public function mount($page = null) {
        $this->page = $page;
        $this->isBlog = $page && $page->slug === 'blog';
    }

    public function render() {
        if ($this->isBlog) {
            $content = explode("[split]", $this->page->content);
            $posts = Util::getBlogs();
            return view('frontend.themes.' . config('app.settings.theme') . '.components.page', [
                'posts' => $posts,
                'content' => $content,
            ]);
        }
        return view('frontend.themes.' . config('app.settings.theme') . '.components.page');
    }
}
