<?php

namespace App\Livewire\Email;

use App\Models\Site;
use App\Services\EmailService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class EmailAccountsTable extends Component
{
    use WithPagination;

    public Site $site;

    public $search = '';

    public $sortField = 'created_at';

    public $sortDirection = 'desc';

    public $filterActive = '';

    public $confirmingDeletion = false;

    public $accountToDelete = null;

    public $showCreateModal = false;

    public $showEditModal = false;

    public $editingAccount = null;

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
    public function render(EmailService $emailService)
    {
        $accounts = $this->site->emailAccounts()
            ->with(['quotaUsage', 'aliases'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('email', 'like', '%'.$this->search.'%')
                        ->orWhere('username', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterActive !== '', function ($query) {
                $query->where('is_active', $this->filterActive);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        // Enrich accounts with quota usage
        foreach ($accounts as $account) {
            $account->quota_info = $emailService->getQuotaUsage($account);
        }

        return view('livewire.email.email-accounts-table', [
            'accounts' => $accounts,
        ]);
    }

    /**
     * Update search query.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Update active filter.
     */
    public function updatedFilterActive(): void
    {
        $this->resetPage();
    }

    /**
     * Sort by field.
     */
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Show create modal.
     */
    public function create(): void
    {
        $this->showCreateModal = true;
    }

    /**
     * Show edit modal.
     */
    public function edit(string $accountId): void
    {
        $this->editingAccount = $accountId;
        $this->showEditModal = true;
    }

    /**
     * Close modals.
     */
    public function closeModals(): void
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->editingAccount = null;
    }

    /**
     * Confirm account deletion.
     */
    public function confirmDelete(string $accountId): void
    {
        $this->confirmingDeletion = true;
        $this->accountToDelete = $accountId;
    }

    /**
     * Cancel deletion.
     */
    public function cancelDelete(): void
    {
        $this->confirmingDeletion = false;
        $this->accountToDelete = null;
    }

    /**
     * Delete an email account.
     */
    public function delete(EmailService $emailService): void
    {
        if (! $this->accountToDelete) {
            return;
        }

        try {
            $emailService->deleteEmailAccount($this->accountToDelete);

            session()->flash('success', 'Email account deleted successfully.');

            $this->dispatch('email-account-deleted');
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting email account: '.$e->getMessage());
        } finally {
            $this->cancelDelete();
        }
    }

    /**
     * Toggle account active status.
     */
    public function toggleActive(string $accountId, EmailService $emailService): void
    {
        try {
            $account = $emailService->getEmailAccount($accountId);

            if (! $account) {
                session()->flash('error', 'Email account not found.');

                return;
            }

            $emailService->updateEmailAccount($accountId, [
                'is_active' => ! $account->is_active,
            ]);

            session()->flash('success', 'Email account '.($account->is_active ? 'disabled' : 'enabled').' successfully.');

            $this->dispatch('email-account-updated');
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating email account: '.$e->getMessage());
        }
    }

    /**
     * Refresh the component when an account is created.
     */
    #[On('email-account-created')]
    public function refresh(): void
    {
        $this->closeModals();
        // Component will auto-refresh
    }

    /**
     * Refresh the component when an account is updated.
     */
    #[On('email-account-updated')]
    public function refreshUpdated(): void
    {
        $this->closeModals();
        // Component will auto-refresh
    }
}
