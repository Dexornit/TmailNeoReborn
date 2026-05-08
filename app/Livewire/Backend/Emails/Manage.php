<?php

namespace App\Livewire\Backend\Emails;

use App\Models\Domain;
use App\Models\Email;
use App\Services\EmailManager;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Manage extends Component
{
    use WithPagination;

    public string $mode = 'random';

    public int $qty = 5;

    public string $username = '';

    public string $manualList = '';

    /** @var array<int,string> */
    public array $selectedDomains = [];

    public string $search = '';

    public string $statusFilter = '';

    public string $domainFilter = '';

    public bool $showResults = false;

    /** @var array<int,string> */
    public array $createdEmails = [];

    /** @var array<int,array<string,string>> */
    public array $skippedEmails = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'domainFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->selectedDomains = Domain::activeDomainNames();
    }

    public function setMode(string $mode): void
    {
        if (in_array($mode, ['random', 'bulk_random', 'manual'], true)) {
            $this->mode = $mode;
        }
    }

    public function toggleDomain(string $domain): void
    {
        if (in_array($domain, $this->selectedDomains, true)) {
            $this->selectedDomains = array_values(array_diff($this->selectedDomains, [$domain]));
        } else {
            $this->selectedDomains[] = $domain;
        }
    }

    public function selectAllDomains(): void
    {
        $this->selectedDomains = Domain::activeDomainNames();
    }

    public function clearDomains(): void
    {
        $this->selectedDomains = [];
    }

    public function generate()
    {
        $this->showResults = false;
        $this->createdEmails = [];
        $this->skippedEmails = [];

        if (empty($this->selectedDomains)) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => __('Please select at least one domain.'),
            ]);

            return null;
        }

        $owner = Auth::user();

        if ($this->mode === 'random') {
            $email = EmailManager::createRandomBulk(1, $this->selectedDomains, $owner, true)->first();
            if ($email) {
                $this->createdEmails = [$email->email];
            } else {
                $this->skippedEmails = [['username' => '(random)', 'reason' => 'failed-to-generate']];
            }
        } elseif ($this->mode === 'bulk_random') {
            $qty = max(1, min(500, $this->qty));
            $created = EmailManager::createRandomBulk($qty, $this->selectedDomains, $owner, true);
            $this->createdEmails = $created->pluck('email')->all();
            if ($created->count() < $qty) {
                $this->skippedEmails[] = [
                    'username' => '(random)',
                    'reason' => 'short-by-'.($qty - $created->count()),
                ];
            }
        } else {
            $list = $this->parseManualList();
            if (empty($list)) {
                $this->dispatch('showAlert', [
                    'type' => 'error',
                    'message' => __('Please enter at least one username.'),
                ]);

                return null;
            }
            $result = EmailManager::createManualBulk($list, $this->selectedDomains, $owner, true);
            $this->createdEmails = $result['created']->pluck('email')->all();
            $this->skippedEmails = $result['skipped'];
        }

        $this->showResults = true;
        $this->dispatch('showAlert', [
            'type' => 'success',
            'message' => __('Created ').count($this->createdEmails).__(' email(s).'),
        ]);

        $this->resetPage();

        return null;
    }

    public function disableEmail(int $id): void
    {
        $email = Email::find($id);
        if ($email) {
            EmailManager::disable($email);
            $this->dispatch('showAlert', [
                'type' => 'success',
                'message' => __('Email disabled.'),
            ]);
        }
    }

    public function enableEmail(int $id): void
    {
        $email = Email::find($id);
        if ($email) {
            EmailManager::restore($email);
            $this->dispatch('showAlert', [
                'type' => 'success',
                'message' => __('Email enabled.'),
            ]);
        }
    }

    public function softDeleteEmail(int $id): void
    {
        $email = Email::find($id);
        if ($email) {
            EmailManager::softDelete($email);
            $this->dispatch('showAlert', [
                'type' => 'success',
                'message' => __('Email moved to trash.'),
            ]);
        }
    }

    public function render()
    {
        $query = Email::query()->latest('id');

        if ($this->search !== '') {
            $needle = strtolower(trim($this->search));
            $query->where(function ($q) use ($needle) {
                $q->where('email', 'like', '%'.$needle.'%')
                    ->orWhere('username', 'like', '%'.$needle.'%');
            });
        }

        if ($this->statusFilter !== '') {
            if ($this->statusFilter === 'trashed') {
                $query->onlyTrashed();
            } else {
                $query->where('status', $this->statusFilter);
            }
        }

        if ($this->domainFilter !== '') {
            $query->where('domain', $this->domainFilter);
        }

        $emails = $query->paginate(20);
        $domains = Domain::where('is_active', true)->orderBy('domain')->get();

        $stats = [
            'total' => Email::count(),
            'active' => Email::where('status', Email::STATUS_ACTIVE)->count(),
            'disabled' => Email::where('status', Email::STATUS_DISABLED)->count(),
            'trashed' => Email::onlyTrashed()->count(),
            'per_domain' => Email::where('status', Email::STATUS_ACTIVE)
                ->selectRaw('domain, COUNT(*) as total')
                ->groupBy('domain')
                ->orderByDesc('total')
                ->limit(10)
                ->get()
                ->map(fn ($row) => ['domain' => $row->domain, 'total' => (int) $row->total])
                ->all(),
        ];

        return view('backend.emails.manage', compact('emails', 'domains', 'stats'));
    }

    /**
     * @return array<int,string>
     */
    private function parseManualList(): array
    {
        $lines = preg_split('/[\r\n,;]+/', $this->manualList) ?: [];
        $clean = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $clean[] = $line;
        }

        return array_values(array_unique($clean));
    }
}
