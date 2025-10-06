<?php

namespace Modules\WordPress\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\WordPress\Models\WordPressInstallation;
use Modules\WordPress\Services\WPCLIService;
use Modules\WordPress\Services\WordPressOrgService;

class PluginManager extends Component
{
    use WithPagination;

    public WordPressInstallation $installation;
    public $plugins = [];
    public $selectedPlugin = null;
    
    // Browse WordPress.org
    public $showBrowseModal = false;
    public $wpOrgPlugins = [];
    public $searchTerm = '';
    public $selectedWpOrgPlugin = null;
    public $showPluginDetails = false;
    
    // Manual install
    public $showInstallModal = false;
    public $newPluginSlug = '';
    public $activateAfterInstall = false;

    public function mount(WordPressInstallation $installation)
    {
        $this->installation = $installation;
        $this->loadPlugins();
    }

    public function loadPlugins()
    {
        $this->plugins = $this->installation->fresh()->plugins()->get()->toArray();
    }

    public function syncPlugins()
    {
        try {
            $wpCli = app(WPCLIService::class);
            $wpCli->syncPlugins($this->installation);
            $this->loadPlugins();
            
            session()->flash('success', 'Plugins synced successfully');
        } catch (\Exception $e) {
            session()->flash('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    public function activatePlugin($slug)
    {
        try {
            $wpCli = app(WPCLIService::class);
            $wpCli->activatePlugin($this->installation, $slug);
            $this->loadPlugins();
            
            session()->flash('success', "Plugin '{$slug}' activated successfully");
        } catch (\Exception $e) {
            session()->flash('error', 'Activation failed: ' . $e->getMessage());
        }
    }

    public function deactivatePlugin($slug)
    {
        try {
            $wpCli = app(WPCLIService::class);
            $wpCli->deactivatePlugin($this->installation, $slug);
            $this->loadPlugins();
            
            session()->flash('success', "Plugin '{$slug}' deactivated successfully");
        } catch (\Exception $e) {
            session()->flash('error', 'Deactivation failed: ' . $e->getMessage());
        }
    }

    public function updatePlugin($slug)
    {
        try {
            $wpCli = app(WPCLIService::class);
            $wpCli->updatePlugin($this->installation, $slug);
            $this->loadPlugins();
            
            session()->flash('success', "Plugin '{$slug}' updated successfully");
        } catch (\Exception $e) {
            session()->flash('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function openInstallModal()
    {
        $this->showInstallModal = true;
        $this->newPluginSlug = '';
        $this->activateAfterInstall = false;
    }

    public function closeInstallModal()
    {
        $this->showInstallModal = false;
    }

    public function installPlugin()
    {
        $this->validate([
            'newPluginSlug' => 'required|string|min:2',
        ]);

        try {
            $wpCli = app(WPCLIService::class);
            $wpCli->installPlugin($this->installation, $this->newPluginSlug, $this->activateAfterInstall);
            $this->loadPlugins();
            
            $this->closeInstallModal();
            session()->flash('success', "Plugin '{$this->newPluginSlug}' installed successfully");
        } catch (\Exception $e) {
            session()->flash('error', 'Installation failed: ' . $e->getMessage());
        }
    }

    public function deletePlugin($slug)
    {
        try {
            $wpCli = app(WPCLIService::class);
            $result = $wpCli->executeCommand($this->installation, "plugin delete {$slug} --deactivate");
            
            if ($result['success']) {
                $this->installation->plugins()->where('slug', $slug)->delete();
                $this->loadPlugins();
                session()->flash('success', "Plugin '{$slug}' deleted successfully");
            } else {
                session()->flash('error', 'Delete failed: ' . $result['output']);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    /**
     * WordPress.org Browse Functions
     */
    public function openBrowseModal()
    {
        $this->showBrowseModal = true;
        $this->searchTerm = '';
        $this->searchWpOrgPlugins();
    }

    public function closeBrowseModal()
    {
        $this->showBrowseModal = false;
        $this->searchTerm = '';
        $this->wpOrgPlugins = [];
    }

    public function searchWpOrgPlugins()
    {
        try {
            $wpOrgService = app(WordPressOrgService::class);
            $result = $wpOrgService->searchPlugins($this->searchTerm, 1, 24);
            
            if ($result['success']) {
                $this->wpOrgPlugins = $result['plugins'] ?? [];
            } else {
                $this->wpOrgPlugins = [];
                session()->flash('error', 'Failed to fetch plugins from WordPress.org');
            }
        } catch (\Exception $e) {
            $this->wpOrgPlugins = [];
            session()->flash('error', 'Search failed: ' . $e->getMessage());
        }
    }

    public function showPluginDetailsModal($slug)
    {
        try {
            $wpOrgService = app(WordPressOrgService::class);
            $result = $wpOrgService->getPluginDetails($slug);
            
            if ($result['success']) {
                $this->selectedWpOrgPlugin = $result['plugin'];
                $this->showPluginDetails = true;
            } else {
                session()->flash('error', 'Failed to load plugin details');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to load details: ' . $e->getMessage());
        }
    }

    public function closePluginDetails()
    {
        $this->showPluginDetails = false;
        $this->selectedWpOrgPlugin = null;
    }

    public function installFromWpOrg($slug, $activate = false)
    {
        try {
            $wpCli = app(WPCLIService::class);
            $result = $wpCli->installPlugin($this->installation, $slug, $activate);
            
            if ($result['success']) {
                $this->loadPlugins();
                $this->closeBrowseModal();
                $this->closePluginDetails();
                
                $message = $activate 
                    ? "Plugin '{$slug}' installed and activated successfully" 
                    : "Plugin '{$slug}' installed successfully";
                    
                session()->flash('success', $message);
            } else {
                session()->flash('error', 'Installation failed: ' . $result['output']);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Installation failed: ' . $e->getMessage());
        }
    }

    public function updatedSearchTerm()
    {
        $this->searchWpOrgPlugins();
    }

    public function render()
    {
        return view('wordpress::livewire.plugin-manager');
    }
}
