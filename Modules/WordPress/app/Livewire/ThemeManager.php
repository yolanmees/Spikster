<?php

namespace Modules\WordPress\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\WordPress\Models\WordPressInstallation;
use Modules\WordPress\Services\WordPressOrgService;
use Modules\WordPress\Services\WPCLIService;

class ThemeManager extends Component
{
    use WithPagination;

    public WordPressInstallation $installation;

    public $themes = [];

    public $selectedTheme = null;

    // Browse WordPress.org
    public $showBrowseModal = false;

    public $wpOrgThemes = [];

    public $searchTerm = '';

    public $selectedWpOrgTheme = null;

    public $showThemeDetails = false;

    // Manual install
    public $showInstallModal = false;

    public $newThemeSlug = '';

    public $activateAfterInstall = false;

    public function mount(WordPressInstallation $installation)
    {
        $this->installation = $installation;
        $this->loadThemes();
    }

    public function loadThemes()
    {
        $this->themes = $this->installation->fresh()->themes()->get()->toArray();
    }

    public function syncThemes()
    {
        try {
            $wpCli = app(WPCLIService::class);
            $wpCli->syncThemes($this->installation);
            $this->loadThemes();

            session()->flash('success', 'Themes synced successfully');
        } catch (\Exception $e) {
            session()->flash('error', 'Sync failed: '.$e->getMessage());
        }
    }

    public function activateTheme($slug)
    {
        try {
            $wpCli = app(WPCLIService::class);
            $wpCli->activateTheme($this->installation, $slug);
            $this->loadThemes();

            session()->flash('success', "Theme '{$slug}' activated successfully");
        } catch (\Exception $e) {
            session()->flash('error', 'Activation failed: '.$e->getMessage());
        }
    }

    public function updateTheme($slug)
    {
        try {
            $wpCli = app(WPCLIService::class);
            $wpCli->updateTheme($this->installation, $slug);
            $this->loadThemes();

            session()->flash('success', "Theme '{$slug}' updated successfully");
        } catch (\Exception $e) {
            session()->flash('error', 'Update failed: '.$e->getMessage());
        }
    }

    public function openInstallModal()
    {
        $this->showInstallModal = true;
        $this->newThemeSlug = '';
        $this->activateAfterInstall = false;
    }

    public function closeInstallModal()
    {
        $this->showInstallModal = false;
    }

    public function installTheme()
    {
        $this->validate([
            'newThemeSlug' => 'required|string|min:2',
        ]);

        try {
            $wpCli = app(WPCLIService::class);
            $wpCli->installTheme($this->installation, $this->newThemeSlug, $this->activateAfterInstall);
            $this->loadThemes();

            $this->closeInstallModal();
            session()->flash('success', "Theme '{$this->newThemeSlug}' installed successfully");
        } catch (\Exception $e) {
            session()->flash('error', 'Installation failed: '.$e->getMessage());
        }
    }

    protected function validateSlug(string $slug): string
    {
        if (! preg_match('/^[a-zA-Z0-9\-_\.]+$/', $slug)) {
            throw new \InvalidArgumentException("Invalid plugin/theme slug: {$slug}");
        }

        return $slug;
    }

    public function deleteTheme($slug)
    {
        try {
            $slug = $this->validateSlug($slug);
            $wpCli = app(WPCLIService::class);
            $result = $wpCli->executeCommand($this->installation, "theme delete {$slug} --force");

            if ($result['success']) {
                $this->installation->themes()->where('slug', $slug)->delete();
                $this->loadThemes();
                session()->flash('success', "Theme '{$slug}' deleted successfully");
            } else {
                session()->flash('error', 'Delete failed: '.$result['output']);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Delete failed: '.$e->getMessage());
        }
    }

    /**
     * WordPress.org Browse Functions
     */
    public function openBrowseModal()
    {
        $this->showBrowseModal = true;
        $this->searchTerm = '';
        $this->searchWpOrgThemes();
    }

    public function closeBrowseModal()
    {
        $this->showBrowseModal = false;
        $this->searchTerm = '';
        $this->wpOrgThemes = [];
    }

    public function searchWpOrgThemes()
    {
        try {
            $wpOrgService = app(WordPressOrgService::class);
            $result = $wpOrgService->searchThemes($this->searchTerm, 1, 24);

            if ($result['success']) {
                $this->wpOrgThemes = $result['themes'] ?? [];
            } else {
                $this->wpOrgThemes = [];
                session()->flash('error', 'Failed to fetch themes from WordPress.org');
            }
        } catch (\Exception $e) {
            $this->wpOrgThemes = [];
            session()->flash('error', 'Search failed: '.$e->getMessage());
        }
    }

    public function showThemeDetailsModal($slug)
    {
        try {
            $wpOrgService = app(WordPressOrgService::class);
            $result = $wpOrgService->getThemeDetails($slug);

            if ($result['success']) {
                $this->selectedWpOrgTheme = $result['theme'];
                $this->showThemeDetails = true;
            } else {
                session()->flash('error', 'Failed to load theme details');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to load details: '.$e->getMessage());
        }
    }

    public function closeThemeDetails()
    {
        $this->showThemeDetails = false;
        $this->selectedWpOrgTheme = null;
    }

    public function installFromWpOrg($slug, $activate = false)
    {
        try {
            $wpCli = app(WPCLIService::class);
            $result = $wpCli->installTheme($this->installation, $slug, $activate);

            if ($result['success']) {
                $this->loadThemes();
                $this->closeBrowseModal();
                $this->closeThemeDetails();

                $message = $activate
                    ? "Theme '{$slug}' installed and activated successfully"
                    : "Theme '{$slug}' installed successfully";

                session()->flash('success', $message);
            } else {
                session()->flash('error', 'Installation failed: '.$result['output']);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Installation failed: '.$e->getMessage());
        }
    }

    public function updatedSearchTerm()
    {
        $this->searchWpOrgThemes();
    }

    public function render()
    {
        return view('wordpress::livewire.theme-manager');
    }
}
