<?php

namespace App\Livewire\Settings;

use App\Models\AuditLog;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class AuditTrailViewer extends Component
{
    use WithPagination;

    public string $search = '';

    public string $eventType = '';

    public string $severity = '';

    public string $userId = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    protected $queryString = ['search', 'eventType', 'severity', 'userId', 'dateFrom', 'dateTo'];

    public function render()
    {
        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%'.$this->search.'%')
                    ->orWhere('ip_address', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->eventType) {
            $query->where('event_type', $this->eventType);
        }

        if ($this->severity) {
            $query->where('severity', $this->severity);
        }

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        if ($this->dateFrom) {
            $query->where('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->where('created_at', '<=', $this->dateTo.' 23:59:59');
        }

        return view('livewire.settings.audit-trail-viewer', [
            'logs' => $query->paginate(50),
            'users' => User::orderBy('name')->get(),
            'eventTypes' => AuditLog::select('event_type')->distinct()->pluck('event_type'),
            'severities' => ['info', 'warning', 'critical'],
        ]);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'eventType', 'severity', 'userId', 'dateFrom', 'dateTo']);
    }
}
