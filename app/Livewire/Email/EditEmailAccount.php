<?php

namespace App\Livewire\Email;

use App\Services\EmailService;
use Livewire\Component;

class EditEmailAccount extends Component
{
    public $accountId;

    public $account;

    public $password = '';

    public $password_confirmation = '';

    public $quota_mb;

    public $enable_spam_filter;

    public $enable_virus_scan;

    public $is_active;

    protected function rules()
    {
        return [
            'password' => 'nullable|string|min:8|confirmed',
            'quota_mb' => 'required|integer|min:100|max:10240',
            'enable_spam_filter' => 'boolean',
            'enable_virus_scan' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected $messages = [
        'password.min' => 'Password must be at least 8 characters.',
        'password.confirmed' => 'Password confirmation does not match.',
        'quota_mb.required' => 'Quota is required.',
        'quota_mb.min' => 'Quota must be at least 100 MB.',
        'quota_mb.max' => 'Quota cannot exceed 10 GB.',
    ];

    /**
     * Mount the component.
     */
    public function mount(string $accountId, EmailService $emailService): void
    {
        $this->accountId = $accountId;
        $this->account = $emailService->getEmailAccount($accountId);

        if (! $this->account) {
            abort(404, 'Email account not found');
        }

        $this->quota_mb = $this->account->quota_mb;
        $this->enable_spam_filter = $this->account->enable_spam_filter;
        $this->enable_virus_scan = $this->account->enable_virus_scan;
        $this->is_active = $this->account->is_active;
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.email.edit-email-account');
    }

    /**
     * Update the email account.
     */
    public function save(EmailService $emailService): void
    {
        $this->validate();

        try {
            $data = [
                'quota_mb' => $this->quota_mb,
                'enable_spam_filter' => $this->enable_spam_filter,
                'enable_virus_scan' => $this->enable_virus_scan,
                'is_active' => $this->is_active,
            ];

            if ($this->password) {
                $data['password'] = $this->password;
            }

            $emailService->updateEmailAccount($this->accountId, $data);

            session()->flash('success', 'Email account updated successfully.');

            $this->dispatch('email-account-updated');

            $this->reset(['password', 'password_confirmation']);
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating email account: '.$e->getMessage());
        }
    }

    /**
     * Reset the form.
     */
    public function resetForm(): void
    {
        $this->quota_mb = $this->account->quota_mb;
        $this->enable_spam_filter = $this->account->enable_spam_filter;
        $this->enable_virus_scan = $this->account->enable_virus_scan;
        $this->is_active = $this->account->is_active;
        $this->reset(['password', 'password_confirmation']);
        $this->resetValidation();
    }
}
