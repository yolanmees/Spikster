<?php

namespace App\Livewire\Site;

use App\Services\SiteService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class SiteTable extends Component
{
    use WithPagination;

    private const CREATE_WAIT_TIMEOUT_SECONDS = 90;

    public $search = '';

    public $sortField = 'created_at';

    public $sortDirection = 'desc';

    public $filterPhp = '';

    public $filterServer = '';

    public $confirmingDeletion = false;

    public $siteToDelete = null;

    public bool $waitingForCreatedSite = false;

    public int $siteCountBeforeCreate = 0;

    public ?int $createWaitStartedAt = null;

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
    public function refreshTable(): void
    {
        // New sites are sorted by creation date; reset to page 1 for visibility.
        $this->resetPage();

        // Start temporary polling while async create job is processing.
        $this->waitingForCreatedSite = true;
        $this->siteCountBeforeCreate = app(SiteService::class)->getAllSitesQuery()->count();
        $this->createWaitStartedAt = time();
    }

    /**
     * Poll only while waiting for a queued site creation to complete.
     */
    public function checkForCreatedSite(SiteService $siteService): void
    {
        if (! $this->waitingForCreatedSite) {
            return;
        }

        $currentCount = $siteService->getAllSitesQuery()->count();
        $isTimedOut = $this->createWaitStartedAt !== null
            && (time() - $this->createWaitStartedAt) >= self::CREATE_WAIT_TIMEOUT_SECONDS;

        if ($currentCount > $this->siteCountBeforeCreate || $isTimedOut) {
            $this->waitingForCreatedSite = false;
            $this->createWaitStartedAt = null;
        }
    }
}
