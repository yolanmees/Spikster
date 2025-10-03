<?php

namespace App\Livewire\Server;

use App\Services\ServerService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ServerTable extends Component
{
    use WithPagination;

    public $search = '';

    public $sortField = 'created_at';

    public $sortDirection = 'desc';

    public $confirmingDeletion = false;

    public $serverToDelete = null;

    /**
     * Create a new component instance.
     */
    public function __construct(
        protected ServerService $serverService
    ) {}

    /**
     * Render the component.
     */
    public function render()
    {
        $servers = $this->serverService->getAllServers()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('ip', 'like', '%'.$this->search.'%')
                        ->orWhere('provider', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.server.server-table', [
            'servers' => $servers,
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
     * Confirm server deletion.
     */
    public function confirmDelete(string $serverId): void
    {
        $this->confirmingDeletion = true;
        $this->serverToDelete = $serverId;
    }

    /**
     * Cancel deletion.
     */
    public function cancelDelete(): void
    {
        $this->confirmingDeletion = false;
        $this->serverToDelete = null;
    }

    /**
     * Delete a server.
     */
    public function delete(): void
    {
        if (! $this->serverToDelete) {
            return;
        }

        try {
            $server = $this->serverService->getServerById($this->serverToDelete);

            if (! $server) {
                session()->flash('error', 'Server niet gevonden.');
                $this->cancelDelete();

                return;
            }

            $this->serverService->deleteServer($server);

            session()->flash('success', 'Server succesvol verwijderd.');

            $this->dispatch('server-deleted');
        } catch (\Exception $e) {
            session()->flash('error', 'Fout bij verwijderen: '.$e->getMessage());
        } finally {
            $this->cancelDelete();
        }
    }

    /**
     * Refresh the component when a server is created.
     */
    #[On('server-created')]
    public function refresh(): void
    {
        // Component will auto-refresh
    }
}
