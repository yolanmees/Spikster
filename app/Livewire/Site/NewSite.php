<?php

namespace App\Livewire\Site;

use App\Jobs\CreateSiteJob;
use App\Services\ServerService;
use App\Services\SiteService;
use Illuminate\Support\Facades\Log;
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
        if ($this->isSubmitting) {
            return;
        }

        $this->isSubmitting = true;

        try {
            $validated = $this->validate();

            Log::info('Site creation form submitted', [
                'domain' => strtolower($validated['domain']),
                'server_id' => $validated['serverId'],
                'php' => $validated['php'],
            ]);

            // Dispatch to queue worker instead of calling synchronously.
            // The Go daemon reloads PHP-FPM during provisioning, which kills the
            // panel's own PHP-FPM worker mid-request if we run synchronously.
            CreateSiteJob::dispatch([
                'server_id' => (int) $validated['serverId'],
                'domain' => strtolower($validated['domain']),
                'php' => $validated['php'],
                'basepath' => $validated['basepath'] ?? '/public',
                'repository' => ! empty($validated['repository']) ? $validated['repository'] : null,
                'branch' => ! empty($validated['branch']) ? $validated['branch'] : null,
            ], auth()->id());

            session()->flash('success', 'Site creation queued. The page will refresh automatically when done.');

            $this->dispatch('site-create-queued');
            $this->dispatch('close-modal');

            $this->resetForm($serverService);
        } catch (ValidationException $e) {
            Log::info('Site creation: validation failed', [
                'errors' => $e->errors(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Site creation failed', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $errorMessage = $e->getMessage();

            if (str_contains($errorMessage, 'daemon is not running')) {
                $errorMessage = 'The Spikster daemon is not running on the server. Please contact your system administrator.';
            } elseif (str_contains($errorMessage, 'Cannot connect to daemon')) {
                $errorMessage = 'The Spikster daemon is not responding. Please check that the daemon service is running on the target server.';
            } elseif (str_contains($errorMessage, 'did not respond in time')) {
                $errorMessage = 'The server timed out while creating the site. The server may be overloaded — please try again.';
            } elseif (str_contains($errorMessage, 'Invalid response')) {
                $errorMessage = 'The daemon returned an unexpected response. Please check the server logs.';
            }

            session()->flash('error', 'Error creating site: '.$errorMessage);
            $this->dispatch('flash-error', message: 'Error creating site: '.$errorMessage);
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
