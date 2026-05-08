<?php

namespace App\Livewire\Frontend;

use App\Models\Domain;
use App\Services\EmailManager;
use App\Services\TMail;
use App\Services\Util;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class PublicLanding extends Component
{
    public string $emailInput = '';

    public array $domains = [];

    public function mount(): void
    {
        $this->domains = Domain::activeDomainNames();
    }

    public function submit()
    {
        $email = strtolower(trim($this->emailInput));

        if (! EmailManager::isValidEmail($email)) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => __('Please enter a valid email address.'),
            ]);

            return null;
        }

        $key = 'inbox-access:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 60)) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => __('Too many requests. Please slow down.'),
            ]);

            return null;
        }
        RateLimiter::hit($key, 60);

        $row = EmailManager::findActive($email);
        if (! $row) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => __('Email not found. Ask the admin to create it first.'),
            ]);

            return null;
        }

        $row->touchLastUsed();
        TMail::ensureEmailInSession($email);

        return redirect(Util::localizeRoute('mailbox'));
    }

    public function render()
    {
        $theme = config('app.settings.theme', 'neobrutalism');
        $view = "frontend.themes.$theme.components.public-landing";
        if (! view()->exists($view)) {
            $view = 'frontend.themes.neobrutalism.components.public-landing';
        }

        return view($view);
    }
}
