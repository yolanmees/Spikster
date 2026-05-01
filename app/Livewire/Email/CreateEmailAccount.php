<?php

namespace App\Livewire\Email;

use App\Models\Site;
use App\Services\EmailService;
use Livewire\Component;

class CreateEmailAccount extends Component
{
    public Site $site;

    public $username = '';

    public $password = '';

    public $password_confirmation = '';

    public $quota_mb = 1024; // 1GB default

    public $enable_spam_filter = true;

    public $enable_virus_scan = true;

    public $is_active = true;

    protected function rules()
    {
        return [
            'username' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9._-]+$/',
                'unique:email_accounts,username,NULL,id,site_id,'.$this->site->id,
            ],
            'password' => 'required|string|min:8|confirmed',
            'quota_mb' => 'required|integer|min:100|max:10240',
            'enable_spam_filter' => 'boolean',
            'enable_virus_scan' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected $messages = [
        'username.required' => 'Username is required.',
        'username.regex' => 'Username can only contain letters, numbers, dots, underscores and hyphens.',
        'username.unique' => 'This email address already exists.',
        'password.required' => 'Password is required.',
        'password.min' => 'Password must be at least 8 characters.',
        'password.confirmed' => 'Password confirmation does not match.',
        'quota_mb.required' => 'Quota is required.',
        'quota_mb.min' => 'Quota must be at least 100 MB.',
        'quota_mb.max' => 'Quota cannot exceed 10 GB.',
    ];

    /**
     * Mount the component.
     */
    public function mount(Site $site): void
    {
        $this->site = $site;
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.email.create-email-account');
    }

    /**
     * Create a new email account.
     */
    public function save(EmailService $emailService): void
    {
        $this->validate();

        try {
            $account = $emailService->createEmailAccount($this->site, [
                'email' => $this->username.'@'.$this->site->domain,
                'password' => $this->password,
                'quota_mb' => $this->quota_mb,
                'spam_filter' => $this->enable_spam_filter,
                'antivirus' => $this->enable_virus_scan,
                'active' => $this->is_active,
            ]);

            session()->flash('success', 'Email account created successfully. IMAP/SMTP settings are available in the account details.');

            $this->dispatch('email-account-created', accountId: $account->id);

            $this->reset(['username', 'password', 'password_confirmation', 'quota_mb', 'enable_spam_filter', 'enable_virus_scan', 'is_active']);
        } catch (\Exception $e) {
            session()->flash('error', 'Error creating email account: '.$e->getMessage());
        }
    }

    /**
     * Reset the form.
     */
    public function resetForm(): void
    {
        $this->reset(['username', 'password', 'password_confirmation', 'quota_mb', 'enable_spam_filter', 'enable_virus_scan', 'is_active']);
        $this->resetValidation();
    }
}
