<?php

namespace App\Livewire\Site;

use App\Jobs\CreateSiteJob;
use App\Services\ServerService;
use App\Services\SiteService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Component;

class NewSite extends Component
{
    #[Validate('required|string|regex:/^[a-zA-Z0-9\-\.]+$/|max:255')]
    public $domain = '';

    #[Validate('required|exists:servers,id')]
    public $serverId = '';

    #[Validate('required|string|in:7.4,8.0,8.1,8.2,8.3,8.4')]
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
     * Mount the component.
     */
    public function mount(ServerService $serverService): void
    {
        $this->servers = $serverService->getAllServers();

        // Set default server if available
        $defaultServer = $serverService->getDefaultServer();
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
    public function submit(SiteService $siteService, ServerService $serverService): void
    {
        // Prevent double submission
        if ($this->isSubmitting) {
            return;
        }

        $this->isSubmitting = true;

        try {
            // Validate the form
            $validated = $this->validate();

            // Dispatch site creation as background job (avoids 502 from php-fpm reload)
            CreateSiteJob::dispatch(
                [
                    'server_id' => (int) $validated['serverId'],
                    'domain' => strtolower($validated['domain']),
                    'php' => $validated['php'],
                    'basepath' => $validated['basepath'] ?? '/public',
                    'repository' => ! empty($validated['repository']) ? $validated['repository'] : null,
                    'branch' => ! empty($validated['branch']) ? $validated['branch'] : null,
                ],
                auth()->id() ?? 0,
            );

            // Flash success message
            session()->flash('success', 'Site is being created. It will appear shortly.');

            // Dispatch event to refresh site list
            $this->dispatch('site-created');

            // Dispatch event to close modal
            $this->dispatch('close-modal');

            // Reset form
            $this->resetForm($serverService);
        } catch (ValidationException $e) {
            // Re-throw validation exceptions
            throw $e;
        } catch (\Exception $e) {
            // Flash error message
            session()->flash('error', 'Error creating site: '.$e->getMessage());
        } finally {
            $this->isSubmitting = false;
        }
    }

    /**
     * Reset the form.
     */
    public function resetForm(?ServerService $serverService = null): void
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
        if ($serverService) {
            $defaultServer = $serverService->getDefaultServer();
            if ($defaultServer) {
                $this->serverId = (string) $defaultServer->id;
            }
        }

        $this->resetValidation();
    }
}
