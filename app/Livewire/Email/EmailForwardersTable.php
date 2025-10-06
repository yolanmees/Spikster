<?php

namespace App\Livewire\Email;

use App\Models\Site;
use App\Services\EmailService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class EmailForwardersTable extends Component
{
    use WithPagination;

    public Site $site;

    public $search = '';

    public $sortField = 'created_at';

    public $sortDirection = 'desc';

    public $confirmingDeletion = false;

    public $forwarderToDelete = null;

    public $showCreateModal = false;

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
        $forwarders = $this->site->emailForwarders()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('source', 'like', '%'.$this->search.'%')
                        ->orWhere('destination', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.email.email-forwarders-table', [
            'forwarders' => $forwarders,
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
     * Close modals.
     */
    public function closeModals(): void
    {
        $this->showCreateModal = false;
    }

    /**
     * Confirm forwarder deletion.
     */
    public function confirmDelete(string $forwarderId): void
    {
        $this->confirmingDeletion = true;
        $this->forwarderToDelete = $forwarderId;
    }

    /**
     * Cancel deletion.
     */
    public function cancelDelete(): void
    {
        $this->confirmingDeletion = false;
        $this->forwarderToDelete = null;
    }

    /**
     * Delete an email forwarder.
     */
    public function delete(EmailService $emailService): void
    {
        if (! $this->forwarderToDelete) {
            return;
        }

        try {
            $emailService->deleteForwarder($this->forwarderToDelete);

            session()->flash('success', 'Email forwarder deleted successfully.');

            $this->dispatch('email-forwarder-deleted');
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting email forwarder: '.$e->getMessage());
        } finally {
            $this->cancelDelete();
        }
    }

    /**
     * Refresh the component when a forwarder is created.
     */
    #[On('email-forwarder-created')]
    public function refresh(): void
    {
        $this->closeModals();
        // Component will auto-refresh
    }
}
