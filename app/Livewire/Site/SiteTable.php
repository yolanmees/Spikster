<?php

namespace App\Livewire\Site;

use App\Services\SiteService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class SiteTable extends Component
{
    use WithPagination;

    public $search = '';

    public $sortField = 'created_at';

    public $sortDirection = 'desc';

    public $filterPhp = '';

    public $filterServer = '';

    public $confirmingDeletion = false;

    public $siteToDelete = null;

    /**
     * Render the component.
     */
    public function render(SiteService $siteService)
    {
        $sites = $siteService->getAllSitesQuery()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('domain', 'like', '%'.$this->search.'%')
                        ->orWhere('username', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterPhp, function ($query) {
                $query->where('php', $this->filterPhp);
            })
            ->when($this->filterServer, function ($query) {
                $query->where('server_id', $this->filterServer);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.site.site-table', [
            'sites' => $sites,
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
     * Update PHP filter.
     */
    public function updatedFilterPhp(): void
    {
        $this->resetPage();
    }

    /**
     * Update server filter.
     */
    public function updatedFilterServer(): void
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
     * Confirm site deletion.
     */
    public function confirmDelete(string $siteId): void
    {
        $this->confirmingDeletion = true;
        $this->siteToDelete = $siteId;
    }

    /**
     * Cancel deletion.
     */
    public function cancelDelete(): void
    {
        $this->confirmingDeletion = false;
        $this->siteToDelete = null;
    }

    /**
     * Delete a site.
     */
    public function delete(SiteService $siteService): void
    {
        if (! $this->siteToDelete) {
            return;
        }

        try {
            $site = $siteService->getSiteById($this->siteToDelete);

            if (! $site) {
                session()->flash('error', 'Site not found.');
                $this->cancelDelete();

                return;
            }

            // Check if it's a panel site
            if ($site->isPanel()) {
                session()->flash('error', 'Panel sites cannot be deleted.');
                $this->cancelDelete();

                return;
            }

            $siteService->deleteSite($site);

            session()->flash('success', 'Site deleted successfully.');

            $this->dispatch('site-deleted');
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting site: '.$e->getMessage());
        } finally {
            $this->cancelDelete();
        }
    }

    /**
     * Refresh the component when a site is created.
     */
    #[On('site-created')]
    public function refresh(): void
    {
        // Component will auto-refresh
    }
}
