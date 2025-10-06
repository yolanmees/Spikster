<?php

namespace App\Livewire\Email;

use App\Models\Site;
use App\Services\EmailService;
use Livewire\Component;

class CreateEmailForwarder extends Component
{
    public Site $site;

    public $source = '';

    public $destination = '';

    public $is_catch_all = false;

    public $keep_copy = false;

    protected function rules()
    {
        return [
            'source' => [
                'required_if:is_catch_all,false',
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9._-]+$/',
            ],
            'destination' => [
                'required',
                'string',
                'max:1000',
            ],
            'is_catch_all' => 'boolean',
            'keep_copy' => 'boolean',
        ];
    }

    protected $messages = [
        'source.required_if' => 'Source is required for non-catch-all forwarders.',
        'source.regex' => 'Source can only contain letters, numbers, dots, underscores and hyphens.',
        'destination.required' => 'Destination is required.',
    ];

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
        return view('livewire.email.create-email-forwarder');
    }

    /**
     * Create a new email forwarder.
     */
    public function save(EmailService $emailService): void
    {
        $this->validate();

        try {
            // For catch-all, source should be "*"
            $source = $this->is_catch_all ? '*' : $this->source;

            $emailService->createForwarder([
                'site_id' => $this->site->id,
                'source' => $source,
                'destination' => $this->destination,
                'is_catch_all' => $this->is_catch_all,
                'keep_copy' => $this->keep_copy,
            ]);

            session()->flash('success', 'Email forwarder created successfully.');

            $this->dispatch('email-forwarder-created');

            $this->reset(['source', 'destination', 'is_catch_all', 'keep_copy']);
        } catch (\Exception $e) {
            session()->flash('error', 'Error creating email forwarder: '.$e->getMessage());
        }
    }

    /**
     * Reset the form.
     */
    public function resetForm(): void
    {
        $this->reset(['source', 'destination', 'is_catch_all', 'keep_copy']);
        $this->resetValidation();
    }

    /**
     * Update catch-all status.
     */
    public function updatedIsCatchAll(): void
    {
        if ($this->is_catch_all) {
            $this->source = '';
        }
    }
}
