<?php

namespace App\Livewire\Domain;

use App\Services\DomainService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class DomainTable extends Component
{
    use WithPagination;

    public $search = '';

    public $sortField = 'created_at';

    public $sortDirection = 'desc';

    public $filterType = ''; // primary, alias, all

    public $filterServer = '';

    /**
     * Render the component.
     */
    public function render(DomainService $domainService)
    {
        $domains = $domainService->getAllDomainsQuery()
            ->when($this->search, function ($query) {
                $query->where('domain', 'like', '%'.$this->search.'%');
            })
            ->when($this->filterType === 'primary', function ($query) {
                $query->where('is_primary', true);
            })
            ->when($this->filterType === 'alias', function ($query) {
                $query->where('is_primary', false);
            })
            ->when($this->filterServer, function ($query) {
                $query->where('server_id', $this->filterServer);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);

        return view('livewire.domain.domain-table', [
            'domains' => $domains,
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
     * Update type filter.
     */
    public function updatedFilterType(): void
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
     * Refresh the component when a domain is created.
     */
    #[On('domain-created')]
    public function refresh(): void
    {
        // Component will auto-refresh
    }
}
