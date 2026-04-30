<?php

namespace Modules\WordPress\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WordPressOrgService
{
    protected string $themeApiUrl = 'https://api.wordpress.org/themes/info/1.2/';
    protected string $pluginApiUrl = 'https://api.wordpress.org/plugins/info/1.2/';
    protected int $cacheDuration = 3600; // 1 hour

    /**
     * Search themes in WordPress.org
     */
    public function searchThemes(string $search = '', int $page = 1, int $perPage = 24, array $filters = []): array
    {
        try {
            $queryArgs = [
                'action' => 'query_themes',
                'page' => $page,
                'per_page' => $perPage,
                'fields[description]' => true,
                'fields[sections]' => false,
                'fields[tested]' => true,
                'fields[requires]' => true,
                'fields[rating]' => true,
                'fields[downloaded]' => true,
                'fields[downloadlink]' => true,
                'fields[last_updated]' => true,
                'fields[homepage]' => true,
                'fields[tags]' => true,
                'fields[screenshot_url]' => true,
                'fields[active_installs]' => true,
            ];

            if (!empty($search)) {
                $queryArgs['search'] = $search;
            }

            // Add optional filters
            if (!empty($filters['tag'])) {
                $queryArgs['tag'] = $filters['tag'];
            }

            if (!empty($filters['browse'])) {
                $queryArgs['browse'] = $filters['browse'];
            } else {
                $queryArgs['browse'] = 'popular';
            }

            $url = $this->themeApiUrl . '?' . http_build_query($queryArgs);

            Log::info('WordPress.org Themes API Request', [
                'url' => $url,
            ]);

            $response = Http::timeout(30)
                ->withOptions(['verify' => false])
                ->get($url);

            Log::info('WordPress.org Themes API Response', [
                'status' => $response->status(),
                'successful' => $response->successful(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'themes' => $data['themes'] ?? [],
                    'info' => $data['info'] ?? [],
                ];
            }

            Log::error('WordPress.org Themes API failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to fetch themes from WordPress.org (HTTP ' . $response->status() . ')',
            ];

        } catch (\Exception $e) {
            Log::error('WordPress.org theme search exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get theme details from WordPress.org
     */
    public function getThemeDetails(string $slug): array
    {
        $cacheKey = "wp_org_theme_{$slug}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($slug) {
            try {
                $params = [
                    'action' => 'theme_information',
                    'request' => [
                        'slug' => $slug,
                        'fields' => [
                            'description' => true,
                            'sections' => true,
                            'tested' => true,
                            'requires' => true,
                            'rating' => true,
                            'ratings' => true,
                            'downloaded' => true,
                            'downloadlink' => true,
                            'last_updated' => true,
                            'homepage' => true,
                            'tags' => true,
                            'screenshot_url' => true,
                            'screenshots' => true,
                            'active_installs' => true,
                        ],
                    ],
                ];

                $response = Http::timeout(10)->post($this->themeApiUrl, $params);

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['error'])) {
                        return [
                            'success' => false,
                            'message' => $data['error'],
                        ];
                    }

                    return [
                        'success' => true,
                        'theme' => $data,
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'Theme not found',
                ];

            } catch (\Exception $e) {
                Log::error('WordPress.org theme details failed', [
                    'slug' => $slug,
                    'error' => $e->getMessage(),
                ]);

                return [
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        });
    }

    /**
     * Search plugins in WordPress.org
     */
    public function searchPlugins(string $search = '', int $page = 1, int $perPage = 24, array $filters = []): array
    {
        try {
            $queryArgs = [
                'action' => 'query_plugins',
                'page' => $page,
                'per_page' => $perPage,
                'fields[description]' => true,
                'fields[sections]' => false,
                'fields[tested]' => true,
                'fields[requires]' => true,
                'fields[rating]' => true,
                'fields[downloaded]' => true,
                'fields[downloadlink]' => true,
                'fields[last_updated]' => true,
                'fields[homepage]' => true,
                'fields[tags]' => true,
                'fields[icons]' => true,
                'fields[banners]' => true,
                'fields[active_installs]' => true,
            ];

            if (!empty($search)) {
                $queryArgs['search'] = $search;
            }

            // Add optional filters
            if (!empty($filters['tag'])) {
                $queryArgs['tag'] = $filters['tag'];
            }
            if (!empty($filters['author'])) {
                $queryArgs['author'] = $filters['author'];
            }
            if (!empty($filters['browse'])) {
                $queryArgs['browse'] = $filters['browse'];
            } else {
                $queryArgs['browse'] = 'popular';
            }

            $url = $this->pluginApiUrl . '?' . http_build_query($queryArgs);

            Log::info('WordPress.org Plugin API Request', [
                'url' => $url,
            ]);

            $response = Http::timeout(30)
                ->withOptions(['verify' => false])
                ->get($url);

            Log::info('WordPress.org Plugin API Response', [
                'status' => $response->status(),
                'successful' => $response->successful(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'plugins' => $data['plugins'] ?? [],
                    'info' => $data['info'] ?? [],
                ];
            }

            Log::error('WordPress.org Plugin API failed', [
                'status' => $response->status(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to fetch plugins from WordPress.org (HTTP ' . $response->status() . ')',
            ];

        } catch (\Exception $e) {
            Log::error('WordPress.org plugin search exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get plugin details from WordPress.org
     */
    public function getPluginDetails(string $slug): array
    {
        $cacheKey = "wp_org_plugin_{$slug}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($slug) {
            try {
                $params = [
                    'action' => 'plugin_information',
                    'request' => [
                        'slug' => $slug,
                        'fields' => [
                            'description' => true,
                            'sections' => true,
                            'tested' => true,
                            'requires' => true,
                            'requires_php' => true,
                            'rating' => true,
                            'ratings' => true,
                            'downloaded' => true,
                            'downloadlink' => true,
                            'last_updated' => true,
                            'added' => true,
                            'homepage' => true,
                            'tags' => true,
                            'donate_link' => true,
                            'icons' => true,
                            'banners' => true,
                            'screenshots' => true,
                            'active_installs' => true,
                            'contributors' => true,
                        ],
                    ],
                ];

                $response = Http::timeout(10)->post($this->pluginApiUrl, $params);

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['error'])) {
                        return [
                            'success' => false,
                            'message' => $data['error'],
                        ];
                    }

                    return [
                        'success' => true,
                        'plugin' => $data,
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'Plugin not found',
                ];

            } catch (\Exception $e) {
                Log::error('WordPress.org plugin details failed', [
                    'slug' => $slug,
                    'error' => $e->getMessage(),
                ]);

                return [
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        });
    }

    /**
     * Get popular themes
     */
    public function getPopularThemes(int $perPage = 12): array
    {
        return $this->searchThemes('', 1, $perPage, []);
    }

    /**
     * Get popular plugins
     */
    public function getPopularPlugins(int $perPage = 12): array
    {
        return $this->searchPlugins('', 1, $perPage, []);
    }

    /**
     * Clear cache for specific theme
     */
    public function clearThemeCache(string $slug): void
    {
        Cache::forget("wp_org_theme_{$slug}");
    }

    /**
     * Clear cache for specific plugin
     */
    public function clearPluginCache(string $slug): void
    {
        Cache::forget("wp_org_plugin_{$slug}");
    }

    /**
     * Clear all WordPress.org cache
     */
    public function clearAllCache(): void
    {
        Cache::flush();
    }
}
