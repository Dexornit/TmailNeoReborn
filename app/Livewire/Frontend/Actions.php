<?php

namespace App\Livewire\Frontend;

use App\Models\Domain;
use App\Models\Email;
use App\Models\Log;
use App\Models\Setting;
use App\Services\EmailManager;
use App\Services\TMail;
use App\Services\Util;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class Actions extends Component
{
    public $in_app = false;

    public $user;

    public $domain;

    public $domains;

    public $email;

    public $emails;

    public $captcha;

    public $memberDomains;

    public bool $canCreate = false;

    public bool $canDelete = false;

    public bool $isGuest = true;

    public bool $hasEmail = false;

    public string $emailInput = '';

    public array $publicDomains = [];

    protected $listeners = ['syncEmail', 'checkReCaptcha3'];

    public function mount()
    {
        $this->domains = Domain::getDomainsForCurrentUser();
        $this->memberDomains = Domain::getMemberOnlyDomains();
        $this->email = TMail::getEmail();
        $this->emails = TMail::getEmails();
        $this->isGuest = ! Auth::check();
        $this->hasEmail = ! empty($this->email);
        $this->canCreate = $this->guestCreateAllowed() || Auth::check();
        $this->canDelete = Auth::check();
        $this->publicDomains = Domain::activeDomainNames();
        $this->validateDomainInEmail();
        if (intval(config('app.settings.default_domain')) && isset($this->domains[intval(config('app.settings.default_domain')) - 1])) {
            $this->domain = $this->domains[intval(config('app.settings.default_domain')) - 1];
        }
    }

    /**
     * Single-page entry point: look up an existing email by address and
     * place it in the session, then trigger an inbox fetch in-place.
     *
     *  - Guests: must point to an existing active row in the `emails` table.
     *  - Logged-in users: an unknown email auto-creates (legacy behavior).
     *
     * No redirect — the App livewire component is updated via syncEmail and
     * fetchMessages events so the inbox refreshes without a page reload.
     */
    public function openInbox()
    {
        $email = strtolower(trim($this->emailInput));

        if (! EmailManager::isValidEmail($email)) {
            return $this->showAlert('error', __('Please enter a valid email address.'));
        }

        $key = 'inbox-access:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 60)) {
            return $this->showAlert('error', __('Too many requests. Please slow down.'));
        }
        RateLimiter::hit($key, 60);

        $row = EmailManager::findActive($email);

        if (! $row) {
            if (! Auth::check()) {
                return $this->showAlert('error', __('Email not found. Ask the admin to create it first.'));
            }

            // Logged-in user can auto-create on the fly via the same path.
            $email = TMail::createCustomEmailFull($email);
        } else {
            $row->touchLastUsed();
            TMail::ensureEmailInSession($email);
        }

        $this->email = $email;
        $this->hasEmail = true;
        $this->emails = TMail::getEmails();
        $this->emailInput = $email;

        // Push the new email into the App livewire component and ask it to
        // refresh — no full-page redirect needed.
        $this->dispatch('syncEmail', email: $email);
        $this->dispatch('fetchMessages');
    }

    public function syncEmail($email)
    {
        $this->email = $email;
        $this->hasEmail = ! empty($email);
        if (! empty($email) && ! in_array($email, $this->emails ?? [], true)) {
            $this->emails = TMail::getEmails();
        }
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    public function checkReCaptcha3($token, $action)
    {
        $response = Http::post('https://www.google.com/recaptcha/api/siteverify?secret='.config('app.settings.recaptcha3.secret_key').'&response='.$token);
        $data = $response->json();
        if ($data['success']) {
            $captcha = $data['score'];
            if ($captcha > 0.5) {
                if ($action == 'create') {
                    $this->create();
                } else {
                    $this->random();
                }
            } else {
                return $this->showAlert('error', __('Captcha Failed! Please try again'));
            }
        } else {
            return $this->showAlert('error', __('Captcha Failed! Error: ').json_encode($data['error-codes']));
        }
    }

    public function create()
    {
        if (! $this->canCreateNow()) {
            return $this->redirectToLogin();
        }
        if (! $this->user) {
            return $this->showAlert('error', __('Please enter Username'));
        }
        $this->checkDomainInUsername();
        if (strlen($this->user) < config('app.settings.custom.min') || strlen($this->user) > config('app.settings.custom.max')) {
            return $this->showAlert('error', __('Username length cannot be less than').' '.config('app.settings.custom.min').' '.__('and greator than').' '.config('app.settings.custom.max'));
        }
        if (! $this->domain) {
            return $this->showAlert('error', __('Please Select a Domain'));
        }
        if (in_array($this->user, config('app.settings.forbidden_ids'))) {
            return $this->showAlert('error', __('Username not allowed'));
        }
        if (! $this->checkEmailLimit()) {
            return $this->showAlert('error', __('You have reached daily limit of MAX ').config('app.settings.email_limit', 5).__(' temp mail'));
        }
        if (! $this->checkUsedEmail()) {
            return $this->showAlert('error', __('Sorry! That email is already been used by someone else. Please try a different email address.'));
        }
        if (! $this->validateCaptcha()) {
            return $this->showAlert('error', __('Invalid Captcha. Please try again'));
        }
        $this->email = TMail::createCustomEmail($this->user, $this->domain);

        return redirect(Util::localizeRoute('mailbox'));
    }

    public function random()
    {
        if (! $this->canCreateNow()) {
            return $this->redirectToLogin();
        }
        if (! $this->checkEmailLimit()) {
            return $this->showAlert('error', __('You have reached daily limit of MAX ').config('app.settings.email_limit', 5).__(' temp mail addresses.'));
        }
        if (! $this->validateCaptcha()) {
            return $this->showAlert('error', __('Invalid Captcha. Please try again'));
        }
        $this->email = TMail::generateRandomEmail();

        return redirect(Util::localizeRoute('mailbox'));
    }

    public function deleteEmail()
    {
        if (! Auth::check()) {
            return $this->redirectToLogin();
        }
        if ($this->email) {
            $row = Email::active()->where('email', $this->email)->first();
            if ($row) {
                $userId = Auth::id();
                if ($row->user_id === null || $row->user_id === $userId) {
                    EmailManager::softDelete($row);
                }
            }
        }
        TMail::removeEmail($this->email);
        if (count($this->emails) == 1 && config('app.settings.after_last_email_delete') == 'redirect_to_homepage') {
            return redirect(Util::localizeRoute('home'));
        }
        $this->email = TMail::getEmail(true);
        $this->emails = TMail::getEmails();

        return redirect(Util::localizeRoute('mailbox'));
    }

    public function render()
    {
        if (count($this->emails) >= intval(config('app.settings.email_limit', 5))) {
            for ($i = 0; $i < (count($this->emails) - intval(config('app.settings.email_limit', 5))); $i++) {
                TMail::removeEmail($this->emails[$i]);
            }
            $this->emails = TMail::getEmails();
            TMail::setEmail($this->email);
        }

        return view('frontend.themes.'.config('app.settings.theme').'.components.actions');
    }

    /**
     * Private Functions
     */
    private function showAlert($type, $message)
    {
        $this->dispatch('showAlert', ['type' => $type, 'message' => $message]);
    }

    /**
     * Whether *guests* are allowed to create or random-generate emails on
     * the frontend. With the new public-OTP landing model, guests are
     * read-only by default; admins can re-enable legacy behavior via the
     * `landing_mode` setting.
     */
    private function guestCreateAllowed(): bool
    {
        return Setting::pick('landing_mode') === 'legacy';
    }

    private function canCreateNow(): bool
    {
        return Auth::check() || $this->guestCreateAllowed();
    }

    private function redirectToLogin()
    {
        $this->dispatch('showAlert', [
            'type' => 'error',
            'message' => __('Please login to create or delete email.'),
        ]);

        return redirect()->route('login');
    }

    /**
     * Don't allow used email
     */
    private function checkUsedEmail()
    {
        if (config('app.settings.disable_used_email', false)) {
            $check = Log::where('email', $this->user.'@'.$this->domain)->where('ip', '<>', request()->ip())->count();
            if ($check > 0) {
                return false;
            }

            return true;
        }

        return true;
    }

    /**
     * Validate Captcha
     */
    private function validateCaptcha()
    {
        if (config('app.settings.captcha') == 'hcaptcha') {
            $response = Http::asForm()->post('https://hcaptcha.com/siteverify', [
                'response' => $this->captcha,
                'secret' => config('app.settings.hcaptcha.secret_key'),
            ])->object();

            return $response->success;
        } elseif (config('app.settings.captcha') == 'recaptcha2') {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'response' => $this->captcha,
                'secret' => config('app.settings.recaptcha2.secret_key'),
            ])->object();

            return $response->success;
        }

        return true;
    }

    /**
     * Check if the user is crossing email limit
     */
    private function checkEmailLimit()
    {
        $logs = Log::select('ip', 'email')->where('ip', request()->ip())->where('created_at', '>', Carbon::now()->subDay())->groupBy('email')->groupBy('ip')->get();
        if (count($logs) >= config('app.settings.email_limit', 5)) {
            return false;
        }

        return true;
    }

    /**
     * Check if Username already consist of Domain
     */
    private function checkDomainInUsername()
    {
        $parts = explode('@', $this->user);
        if (isset($parts[1])) {
            if (in_array($parts[1], $this->domains)) {
                $this->domain = $parts[1];
            }
            $this->user = $parts[0];
        }
    }

    /**
     * Validate if Domain in Email Exist
     */
    private function validateDomainInEmail()
    {
        $data = explode('@', $this->email);
        if (isset($data[1])) {
            $domain = $data[1];
            $domains = Domain::getDomainsForCurrentUser();
            if (! in_array($domain, $domains)) {
                $key = array_search($this->email, $this->emails);
                TMail::removeEmail($this->email);
                if ($key == 0 && count($this->emails) == 1 && config('app.settings.after_last_email_delete') == 'redirect_to_homepage') {
                    return redirect(Util::localizeRoute('home'));
                } else {
                    return redirect(Util::localizeRoute('mailbox'));
                }
            }
        }
    }
}
