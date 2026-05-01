<?php

namespace Modules\WordPress\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\WordPress\Models\WordPressInstallation;
use Modules\WordPress\Services\WordPressInstallationService;
use Modules\WordPress\Services\WordPressOrgService;
use Modules\WordPress\Services\WPCLIService;

class WordPressApiController extends Controller
{
    public function __construct(
        private WordPressInstallationService $installationService,
        private WPCLIService $wpCliService,
        private WordPressOrgService $wpOrgService
    ) {}

    /**
     * List all WordPress installations
     */
    public function index(Request $request): JsonResponse
    {
        $query = WordPressInstallation::with(['site', 'themes', 'plugins']);

        if ($request->has('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $installations = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return response()->json([
            'success' => true,
            'data' => $installations,
        ]);
    }

    /**
     * Show single WordPress installation
     */
    public function show(string $id): JsonResponse
    {
        $installation = WordPressInstallation::with([
            'site',
            'database',
            'databaseUser',
            'themes',
            'plugins',
            'updates',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $installation,
        ]);
    }

    /**
     * Install new WordPress
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'site_id' => 'required|exists:sites,site_id',
            'path' => 'required|string',
            'url' => 'nullable|url',
            'username' => 'required|string|min:3',
            'password' => 'required|string|min:8',
            'locale' => 'nullable|string',
        ]);

        try {
            $installation = $this->installationService->install($validated);

            return response()->json([
                'success' => true,
                'message' => 'WordPress installed successfully',
                'data' => $installation->load(['site', 'database', 'databaseUser']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Installation failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Uninstall WordPress
     */
    public function destroy(string $id): JsonResponse
    {
        $installation = WordPressInstallation::findOrFail($id);

        try {
            $this->installationService->uninstall($installation);

            return response()->json([
                'success' => true,
                'message' => 'WordPress uninstalled successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Uninstall failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get WordPress info (version, etc.)
     */
    public function getInfo(string $id): JsonResponse
    {
        $installation = WordPressInstallation::findOrFail($id);

        try {
            $info = $this->wpCliService->getInfo($installation);

            return response()->json([
                'success' => true,
                'data' => $info,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get WordPress info: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * List themes
     */
    public function listThemes(string $id): JsonResponse
    {
        $installation = WordPressInstallation::with('themes')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $installation->themes,
        ]);
    }

    /**
     * Sync themes from WordPress
     */
    public function syncThemes(string $id): JsonResponse
    {
        $installation = WordPressInstallation::findOrFail($id);

        try {
            $this->wpCliService->syncThemes($installation);

            return response()->json([
                'success' => true,
                'message' => 'Themes synced successfully',
                'data' => $installation->fresh()->themes,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Activate theme
     */
    public function activateTheme(string $id, string $slug): JsonResponse
    {
        $installation = WordPressInstallation::findOrFail($id);

        try {
            $this->wpCliService->activateTheme($installation, $slug);

            return response()->json([
                'success' => true,
                'message' => "Theme '{$slug}' activated successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Activation failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Install theme
     */
    public function installTheme(string $id, string $slug, Request $request): JsonResponse
    {
        $installation = WordPressInstallation::findOrFail($id);
        $activate = $request->input('activate', false);

        try {
            $this->wpCliService->installTheme($installation, $slug, $activate);

            return response()->json([
                'success' => true,
                'message' => "Theme '{$slug}' installed successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Installation failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update theme
     */
    public function updateTheme(string $id, string $slug): JsonResponse
    {
        $installation = WordPressInstallation::findOrFail($id);

        try {
            $this->wpCliService->updateTheme($installation, $slug);

            return response()->json([
                'success' => true,
                'message' => "Theme '{$slug}' updated successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete theme
     */
    public function deleteTheme(string $id, string $slug): JsonResponse
    {
        $installation = WordPressInstallation::findOrFail($id);

        try {
            $result = $this->wpCliService->executeCommand($installation, "theme delete {$slug} --force");

            if ($result['success']) {
                // Remove from database
                $installation->themes()->where('slug', $slug)->delete();
            }

            return response()->json([
                'success' => $result['success'],
                'message' => $result['success']
                    ? "Theme '{$slug}' deleted successfully"
                    : 'Delete failed: '.$result['output'],
            ], $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * List plugins
     */
    public function listPlugins(string $id): JsonResponse
    {
        $installation = WordPressInstallation::with('plugins')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $installation->plugins,
        ]);
    }

    /**
     * Sync plugins from WordPress
     */
    public function syncPlugins(string $id): JsonResponse
    {
        $installation = WordPressInstallation::findOrFail($id);

        try {
            $this->wpCliService->syncPlugins($installation);

            return response()->json([
                'success' => true,
                'message' => 'Plugins synced successfully',
                'data' => $installation->fresh()->plugins,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Activate plugin
     */
    public function activatePlugin(string $id, string $slug): JsonResponse
    {
        $installation = WordPressInstallation::findOrFail($id);

        try {
            $this->wpCliService->activatePlugin($installation, $slug);

            return response()->json([
                'success' => true,
                'message' => "Plugin '{$slug}' activated successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Activation failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Deactivate plugin
     */
    public function deactivatePlugin(string $id, string $slug): JsonResponse
    {
        $installation = WordPressInstallation::findOrFail($id);

        try {
            $this->wpCliService->deactivatePlugin($installation, $slug);

            return response()->json([
                'success' => true,
                'message' => "Plugin '{$slug}' deactivated successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Deactivation failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Install plugin
     */
    public function installPlugin(string $id, string $slug, Request $request): JsonResponse
    {
        $installation = WordPressInstallation::findOrFail($id);
        $activate = $request->input('activate', false);

        try {
            $this->wpCliService->installPlugin($installation, $slug, $activate);

            return response()->json([
                'success' => true,
                'message' => "Plugin '{$slug}' installed successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Installation failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update plugin
     */
    public function updatePlugin(string $id, string $slug): JsonResponse
    {
        $installation = WordPressInstallation::findOrFail($id);

        try {
            $this->wpCliService->updatePlugin($installation, $slug);

            return response()->json([
                'success' => true,
                'message' => "Plugin '{$slug}' updated successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete plugin
     */
    public function deletePlugin(string $id, string $slug): JsonResponse
    {
        $installation = WordPressInstallation::findOrFail($id);

        try {
            $result = $this->wpCliService->executeCommand($installation, "plugin delete {$slug} --deactivate");

            if ($result['success']) {
                // Remove from database
                $installation->plugins()->where('slug', $slug)->delete();
            }

            return response()->json([
                'success' => $result['success'],
                'message' => $result['success']
                    ? "Plugin '{$slug}' deleted successfully"
                    : 'Delete failed: '.$result['output'],
            ], $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check for updates
     */
    public function checkUpdates(string $id): JsonResponse
    {
        $installation = WordPressInstallation::findOrFail($id);

        try {
            // Check core updates
            $coreUpdate = $this->wpCliService->checkCoreUpdate($installation);

            // Sync themes and plugins to get latest update info
            $this->wpCliService->syncThemes($installation);
            $this->wpCliService->syncPlugins($installation);

            $installation = $installation->fresh();

            return response()->json([
                'success' => true,
                'data' => [
                    'core' => $coreUpdate,
                    'themes' => $installation->themes()->where('update_available', '!=', null)->get(),
                    'plugins' => $installation->plugins()->where('update_available', '!=', null)->get(),
                    'total_updates' => $installation->getUpdatesCount(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check updates: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update WordPress core
     */
    public function updateCore(string $id): JsonResponse
    {
        $installation = WordPressInstallation::findOrFail($id);

        try {
            $this->wpCliService->updateCore($installation);

            return response()->json([
                'success' => true,
                'message' => 'WordPress core updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update all (core, themes, plugins)
     */
    public function updateAll(string $id): JsonResponse
    {
        $installation = WordPressInstallation::findOrFail($id);

        try {
            $results = [
                'core' => false,
                'themes' => [],
                'plugins' => [],
            ];

            // Update core
            try {
                $this->wpCliService->updateCore($installation);
                $results['core'] = true;
            } catch (\Exception $e) {
                // Core might not need update
            }

            // Update all themes with updates
            $installation->fresh();
            foreach ($installation->themes()->whereNotNull('update_available')->get() as $theme) {
                try {
                    $this->wpCliService->updateTheme($installation, $theme->slug);
                    $results['themes'][] = $theme->slug;
                } catch (\Exception $e) {
                    // Skip failed updates
                }
            }

            // Update all plugins with updates
            foreach ($installation->plugins()->whereNotNull('update_available')->get() as $plugin) {
                try {
                    $this->wpCliService->updatePlugin($installation, $plugin->slug);
                    $results['plugins'][] = $plugin->slug;
                } catch (\Exception $e) {
                    // Skip failed updates
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Updates completed',
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Browse WordPress.org themes
     */
    public function browseWpOrgThemes(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => 'nullable|string',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'tag' => 'nullable|string',
        ]);

        try {
            $result = $this->wpOrgService->searchThemes(
                $validated['search'] ?? '',
                $validated['page'] ?? 1,
                $validated['per_page'] ?? 24,
                ['tag' => $validated['tag'] ?? null]
            );

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to fetch themes',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => $result['themes'],
                'info' => $result['info'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to browse themes: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single WordPress.org theme details
     */
    public function getWpOrgTheme(string $slug): JsonResponse
    {
        try {
            $result = $this->wpOrgService->getThemeDetails($slug);

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Theme not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $result['theme'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch theme: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Browse WordPress.org plugins
     */
    public function browseWpOrgPlugins(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => 'nullable|string',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'tag' => 'nullable|string',
            'author' => 'nullable|string',
        ]);

        try {
            $result = $this->wpOrgService->searchPlugins(
                $validated['search'] ?? '',
                $validated['page'] ?? 1,
                $validated['per_page'] ?? 24,
                [
                    'tag' => $validated['tag'] ?? null,
                    'author' => $validated['author'] ?? null,
                ]
            );

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to fetch plugins',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => $result['plugins'],
                'info' => $result['info'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to browse plugins: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single WordPress.org plugin details
     */
    public function getWpOrgPlugin(string $slug): JsonResponse
    {
        try {
            $result = $this->wpOrgService->getPluginDetails($slug);

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Plugin not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $result['plugin'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch plugin: '.$e->getMessage(),
            ], 500);
        }
    }
}
