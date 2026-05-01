<?php

namespace Modules\WordPress\Livewire;

use App\Models\Site;
use Livewire\Component;
use Modules\WordPress\Services\WordPressInstallationService;

class InstallationWizard extends Component
{
    public $currentStep = 1;

    public $totalSteps = 3;

    // Step 1: Site Selection
    public $site_id = '';

    public $path = '/';

    public $url = '';

    // Step 2: Admin Credentials
    public $username = 'admin';

    public $password = '';

    // Step 3: Configuration
    public $locale = 'en_US';

    public $auto_update = false;

    // Installation
    public $isInstalling = false;

    public $installationComplete = false;

    public $installation = null;

    protected $rules = [
        'site_id' => 'required|exists:sites,site_id',
        'path' => 'required|string',
        'url' => 'nullable|url',
        'username' => 'required|string|min:3',
        'password' => 'required|string|min:8',
        'locale' => 'required|string',
    ];

    public function mount()
    {
        $this->currentStep = 1;
    }

    public function nextStep()
    {
        $this->validateCurrentStep();

        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    protected function validateCurrentStep()
    {
        $rules = [];

        if ($this->currentStep === 1) {
            $rules = [
                'site_id' => 'required|exists:sites,site_id',
                'path' => 'required|string',
                'url' => 'nullable|url',
            ];
        } elseif ($this->currentStep === 2) {
            $rules = [
                'username' => 'required|string|min:3',
                'password' => 'required|string|min:8',
            ];
        }

        $this->validate($rules);
    }

    public function install()
    {
        $this->validate();
        $this->isInstalling = true;

        try {
            $service = app(WordPressInstallationService::class);

            $this->installation = $service->install([
                'site_id' => $this->site_id,
                'path' => $this->path,
                'url' => $this->url,
                'username' => $this->username,
                'password' => $this->password,
                'locale' => $this->locale,
                'auto_update' => $this->auto_update,
            ]);

            $this->installationComplete = true;
            session()->flash('success', 'WordPress installed successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Installation failed: '.$e->getMessage());
            $this->isInstalling = false;
        }
    }

    public function getSitesProperty()
    {
        return Site::all();
    }

    public function getSelectedSiteProperty()
    {
        if (! $this->site_id) {
            return null;
        }

        return Site::where('site_id', $this->site_id)->first();
    }

    public function render()
    {
        return view('wordpress::livewire.installation-wizard');
    }
}
