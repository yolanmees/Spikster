<?php

namespace App\Livewire\Settings;

use App\Models\Webhook;
use App\Services\WebhookService;
use Livewire\Component;

class WebhookManager extends Component
{
    public bool $showForm = false;

    public ?int $editingWebhookId = null;

    public string $name = '';

    public string $url = '';

    public string $secret = '';

    public array $selectedEvents = [];

    public function render(WebhookService $webhookService)
    {
        $webhooks = Webhook::where('user_id', auth()->id())
            ->withCount('deliveries')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.settings.webhook-manager', [
            'webhooks' => $webhooks,
            'availableEvents' => $webhookService->availableEvents(),
        ]);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
        $this->editingWebhookId = null;
    }

    public function edit(int $id): void
    {
        $webhook = Webhook::where('user_id', auth()->id())->findOrFail($id);

        $this->editingWebhookId = $webhook->id;
        $this->name = $webhook->name;
        $this->url = $webhook->url;
        $this->secret = '';
        $this->selectedEvents = $webhook->events ?? [];
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|max:255',
            'url' => 'required|url|max:1024',
            'selectedEvents' => 'required|array|min:1',
        ]);

        $data = [
            'name' => $this->name,
            'url' => $this->url,
            'events' => $this->selectedEvents,
        ];

        if ($this->secret) {
            $data['secret'] = $this->secret;
        }

        if ($this->editingWebhookId) {
            $webhook = Webhook::where('user_id', auth()->id())->findOrFail($this->editingWebhookId);
            $webhook->update($data);
            $this->dispatch('notify', message: 'Webhook updated', type: 'success');
        } else {
            $data['user_id'] = auth()->id();
            Webhook::create($data);
            $this->dispatch('notify', message: 'Webhook created', type: 'success');
        }

        $this->showForm = false;
        $this->resetForm();
    }

    public function toggle(int $id): void
    {
        $webhook = Webhook::where('user_id', auth()->id())->findOrFail($id);
        $webhook->update(['is_active' => ! $webhook->is_active]);
    }

    public function test(int $id, WebhookService $webhookService): void
    {
        $webhook = Webhook::where('user_id', auth()->id())->findOrFail($id);
        $delivery = $webhookService->test($webhook);

        if ($delivery->status === 'success') {
            $this->dispatch('notify', message: 'Webhook test sent successfully', type: 'success');
        } else {
            $this->dispatch('notify', message: 'Webhook test failed: '.($delivery->error_message ?? 'Unknown error'), type: 'error');
        }
    }

    public function delete(int $id): void
    {
        $webhook = Webhook::where('user_id', auth()->id())->findOrFail($id);
        $webhook->delete();
        $this->dispatch('notify', message: 'Webhook deleted', type: 'success');
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->url = '';
        $this->secret = '';
        $this->selectedEvents = [];
        $this->editingWebhookId = null;
    }
}
