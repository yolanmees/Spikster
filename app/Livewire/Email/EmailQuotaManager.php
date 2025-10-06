<?php

namespace App\Livewire\Email;

use App\Services\EmailService;
use Livewire\Component;

class EmailQuotaManager extends Component
{
    public $accountId;

    public $account;

    public $quotaUsage;

    public $showUpdateModal = false;

    public $newQuota;

    protected function rules()
    {
        return [
            'newQuota' => 'required|integer|min:100|max:10240',
        ];
    }

    protected $messages = [
        'newQuota.required' => 'Quota is required.',
        'newQuota.min' => 'Quota must be at least 100 MB.',
        'newQuota.max' => 'Quota cannot exceed 10 GB.',
    ];

    /**
     * Mount the component.
     */
    public function mount(string $accountId, EmailService $emailService): void
    {
        $this->accountId = $accountId;
        $this->loadQuotaData($emailService);
    }

    /**
     * Load quota data.
     */
    public function loadQuotaData(EmailService $emailService): void
    {
        $this->account = $emailService->getEmailAccount($this->accountId);

        if (! $this->account) {
            abort(404, 'Email account not found');
        }

        $this->quotaUsage = $emailService->getQuotaUsage($this->accountId);
        $this->newQuota = $this->account->quota_mb;
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.email.email-quota-manager');
    }

    /**
     * Show update quota modal.
     */
    public function showUpdate(): void
    {
        $this->newQuota = $this->account->quota_mb;
        $this->showUpdateModal = true;
    }

    /**
     * Close modals.
     */
    public function closeModals(): void
    {
        $this->showUpdateModal = false;
    }

    /**
     * Update quota.
     */
    public function updateQuota(EmailService $emailService): void
    {
        $this->validate();

        try {
            $emailService->updateEmailAccount($this->accountId, [
                'quota_mb' => $this->newQuota,
            ]);

            session()->flash('success', 'Quota updated successfully.');

            $this->loadQuotaData($emailService);
            $this->closeModals();

            $this->dispatch('email-quota-updated');
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating quota: '.$e->getMessage());
        }
    }

    /**
     * Refresh quota data.
     */
    public function refresh(EmailService $emailService): void
    {
        $this->loadQuotaData($emailService);
    }

    /**
     * Get progress bar color based on usage percentage.
     */
    public function getProgressBarColor(): string
    {
        if (! $this->quotaUsage) {
            return 'bg-gray-400';
        }

        $percentage = $this->quotaUsage['percentage'];

        if ($percentage >= 95) {
            return 'bg-red-600';
        } elseif ($percentage >= 80) {
            return 'bg-yellow-500';
        }

        return 'bg-green-500';
    }
}
