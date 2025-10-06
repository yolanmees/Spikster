<?php

namespace Modules\WordPress\Livewire;

use Livewire\Component;
use Modules\WordPress\Models\WordPressInstallation;
use Modules\WordPress\Services\WPCLIService;

class UpdatesManager extends Component
{
    public WordPressInstallation $installation;
    public $coreUpdate = null;
    public $themeUpdates = [];
    public $pluginUpdates = [];
    public $isChecking = false;
    public $isUpdating = false;

    public function mount(WordPressInstallation $installation)
    {
        $this->installation = $installation;
        $this->loadUpdates();
    }

    public function loadUpdates()
    {
        $this->themeUpdates = $this->installation->themes()
            ->whereNotNull('update_available')
            ->get()
            ->toArray();

        $this->pluginUpdates = $this->installation->plugins()
            ->whereNotNull('update_available')
            ->get()
            ->toArray();
    }

    public function checkUpdates()
    {
        $this->isChecking = true;

        try {
            $wpCli = app(WPCLIService::class);
            
            // Sync themes and plugins to get latest update info (also updates WP version)
            $wpCli->syncThemes($this->installation);
            $wpCli->syncPlugins($this->installation);
            
            // Check core update
            $coreResult = $wpCli->checkCoreUpdate($this->installation);
            
            // Only set coreUpdate if there's actually an update available
            if ($coreResult['success'] && $coreResult['has_update']) {
                $this->coreUpdate = [
                    'version' => $coreResult['new_version'],
                    'current_version' => $coreResult['current_version'],
                ];
            } else {
                $this->coreUpdate = null;
            }
            
            $this->installation = $this->installation->fresh();
            $this->loadUpdates();
            
            session()->flash('success', 'Updates checked successfully');
        } catch (\Exception $e) {
            session()->flash('error', 'Check failed: ' . $e->getMessage());
        } finally {
            $this->isChecking = false;
        }
    }

    public function updateCore()
    {
        $this->isUpdating = true;

        try {
            $wpCli = app(WPCLIService::class);
            $wpCli->updateCore($this->installation);
            
            $this->coreUpdate = null;
            session()->flash('success', 'WordPress core updated successfully');
        } catch (\Exception $e) {
            session()->flash('error', 'Core update failed: ' . $e->getMessage());
        } finally {
            $this->isUpdating = false;
        }
    }

    public function updateTheme($slug)
    {
        try {
            $wpCli = app(WPCLIService::class);
            $wpCli->updateTheme($this->installation, $slug);
            
            $this->loadUpdates();
            session()->flash('success', "Theme '{$slug}' updated successfully");
        } catch (\Exception $e) {
            session()->flash('error', 'Theme update failed: ' . $e->getMessage());
        }
    }

    public function updatePlugin($slug)
    {
        try {
            $wpCli = app(WPCLIService::class);
            $wpCli->updatePlugin($this->installation, $slug);
            
            $this->loadUpdates();
            session()->flash('success', "Plugin '{$slug}' updated successfully");
        } catch (\Exception $e) {
            session()->flash('error', 'Plugin update failed: ' . $e->getMessage());
        }
    }

    public function updateAll()
    {
        $this->isUpdating = true;

        try {
            $wpCli = app(WPCLIService::class);
            $updated = [];

            // Update core if available
            if ($this->coreUpdate) {
                try {
                    $wpCli->updateCore($this->installation);
                    $updated[] = 'WordPress Core';
                    $this->coreUpdate = null;
                } catch (\Exception $e) {
                    // Continue with other updates
                }
            }

            // Update all themes
            foreach ($this->themeUpdates as $theme) {
                try {
                    $wpCli->updateTheme($this->installation, $theme['slug']);
                    $updated[] = "Theme: {$theme['name']}";
                } catch (\Exception $e) {
                    // Continue with other updates
                }
            }

            // Update all plugins
            foreach ($this->pluginUpdates as $plugin) {
                try {
                    $wpCli->updatePlugin($this->installation, $plugin['slug']);
                    $updated[] = "Plugin: {$plugin['name']}";
                } catch (\Exception $e) {
                    // Continue with other updates
                }
            }

            $this->loadUpdates();
            
            $count = count($updated);
            session()->flash('success', "Updated {$count} item(s) successfully");
        } catch (\Exception $e) {
            session()->flash('error', 'Update all failed: ' . $e->getMessage());
        } finally {
            $this->isUpdating = false;
        }
    }

    public function getTotalUpdatesProperty()
    {
        return ($this->coreUpdate ? 1 : 0) + count($this->themeUpdates) + count($this->pluginUpdates);
    }

    public function render()
    {
        return view('wordpress::livewire.updates-manager');
    }
}
