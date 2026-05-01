<?php

namespace App\Livewire\Server;

use App\Services\ServerService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Component;

class NewServer extends Component
{
    #[Validate('required|string|max:255')]
    public $serverName = '';

    #[Validate('required|ip')]
    public $serverIp = '';

    #[Validate('required|string|max:255')]
    public $serverProvider = '';

    #[Validate('nullable|string|max:255')]
    public $serverApiKey = '';

    #[Validate('nullable|string|max:255')]
    public $serverLocation = '';

    #[Validate('required|integer|min:1|max:65535')]
    public $serverSshPort = 22;

    #[Validate('required|string|min:8')]
    public $serverSshPassword = '';

    public $isSubmitting = false;

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.server.new-server');
    }

    /**
     * Submit the form.
     */
    public function submit(ServerService $serverService): void
    {
        // Prevent double submission
        if ($this->isSubmitting) {
            return;
        }

        $this->isSubmitting = true;

        try {
            // Validate the form
            $validated = $this->validate();

            // Create server using service
            $server = $serverService->createServer([
                'name' => $validated['serverName'],
                'ip' => $validated['serverIp'],
                'provider' => $validated['serverProvider'],
                'location' => $validated['serverLocation'] ?? null,
                'api_key' => $validated['serverApiKey'] ?? null,
                'ssh_port' => $validated['serverSshPort'],
                'password' => $validated['serverSshPassword'],
                'database' => 'spikster',
                'status' => 0, // Not installed yet
            ]);

            // Flash success message
            session()->flash('success', 'Server succesvol aangemaakt.');

            // Dispatch event to refresh server list
            $this->dispatch('server-created');

            // Close modal
            $this->dispatch('close-modal');

            // Reset form
            $this->reset([
                'serverName',
                'serverIp',
                'serverProvider',
                'serverApiKey',
                'serverLocation',
                'serverSshPort',
                'serverSshPassword',
            ]);

            $this->serverSshPort = 22;
        } catch (ValidationException $e) {
            // Re-throw validation exceptions
            throw $e;
        } catch (\Exception $e) {
            // Flash error message
            session()->flash('error', 'Fout bij aanmaken server: '.$e->getMessage());
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
            'serverName',
            'serverIp',
            'serverProvider',
            'serverApiKey',
            'serverLocation',
            'serverSshPort',
            'serverSshPassword',
        ]);

        $this->serverSshPort = 22;
        $this->resetValidation();
    }
}
