<?php

namespace App\Livewire\Site;

use App\Services\ServerService;
use App\Services\SiteService;
use Livewire\Attributes\Validate;
use Livewire\Component;

class NewSite extends Component
{
    #[Validate('required|string|regex:/^[a-z0-9\-\.]+$/|max:255')]
    public $domain = '';

    #[Validate('required|exists:servers,id')]
    public $serverId = '';

    #[Validate('required|string|in:7.4,8.0,8.1,8.2,8.3')]
    public $php = '8.3';

    #[Validate('nullable|string|max:255')]
    public $basepath = '/public';

    #[Validate('nullable|url|max:500')]
    public $repository = '';

    #[Validate('nullable|required_with:repository|string|max:255')]
    public $branch = 'main';

    public $isSubmitting = false;

    public $servers = [];

    /**
     * Create a new component instance.
     */
    public function __construct(
        protected SiteService $siteService,
        protected ServerService $serverService
    ) {}

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->servers = $this->serverService->getAllServers()->get();

        // Set default server if available
        $defaultServer = $this->serverService->getDefaultServer();
        if ($defaultServer) {
            $this->serverId = (string) $defaultServer->id;
        }
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.site.new-site');
    }

    /**
     * Submit the form.
     */
    public function submit(): void
    {
        // Prevent double submission
        if ($this->isSubmitting) {
            return;
        }

        $this->isSubmitting = true;

        try {
            // Validate the form
            $validated = $this->validate();

            // Create site using service
            $site = $this->siteService->createSite([
                'server_id' => $validated['serverId'],
                'domain' => $validated['domain'],
                'php' => $validated['php'],
                'basepath' => $validated['basepath'] ?? '/public',
                'repository' => $validated['repository'] ?? null,
                'branch' => $validated['branch'] ?? null,
            ]);

            // Flash success message
            session()->flash('success', 'Site succesvol aangemaakt.');

            // Dispatch event to refresh site list
            $this->dispatch('site-created');

            // Reset form
            $this->resetForm();
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exceptions
            throw $e;
        } catch (\Exception $e) {
            // Flash error message
            session()->flash('error', 'Fout bij aanmaken site: '.$e->getMessage());
        } finally {
            $this->isSubmitting = false;
        }
    }

    /**
     * Reset the form.
     */
    public function resetForm(): void
    {
        $this->reset([
            'domain',
            'php',
            'basepath',
            'repository',
            'branch',
        ]);

        $this->php = '8.3';
        $this->basepath = '/public';
        $this->branch = 'main';

        // Reset to default server
        $defaultServer = $this->serverService->getDefaultServer();
        if ($defaultServer) {
            $this->serverId = (string) $defaultServer->id;
        }

        $this->resetValidation();
    }
}
