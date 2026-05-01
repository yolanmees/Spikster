<?php

namespace Modules\WordPress\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\WordPress\Models\WordPressInstallation;
use Modules\WordPress\Services\WordPressInstallationService;
use Modules\WordPress\Services\WPCLIService;

class WordPressController extends Controller
{
    public function __construct(
        protected WordPressInstallationService $installationService,
        protected WPCLIService $wpCLIService
    ) {}

    public function index(Request $request)
    {
        $query = WordPressInstallation::query()
            ->with(['site', 'database', 'themes', 'plugins', 'pendingUpdates'])
            ->orderBy('created_at', 'desc');

        if ($request->has('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $installations = $query->paginate(15);

        return view('wordpress::installations.index', compact('installations'));
    }

    public function show($id)
    {
        $installation = WordPressInstallation::with([
            'site',
            'database',
            'databaseUser',
            'themes',
            'plugins',
            'updates',
        ])->findOrFail($id);

        return view('wordpress::installations.show', compact('installation'));
    }

    public function create(Request $request)
    {
        return view('wordpress::installations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'site_id' => 'required|exists:sites,site_id',
            'path' => 'required|string',
            'username' => 'required|string|min:3',
            'password' => 'required|string|min:8',
            'url' => 'nullable|url',
            'locale' => 'nullable|string',
        ]);

        $result = $this->installationService->install($request->all());

        if ($result['success']) {
            return redirect()
                ->route('wordpress.show', $result['installation']->id)
                ->with('success', $result['message']);
        }

        return back()
            ->withErrors(['error' => $result['message']])
            ->withInput();
    }

    public function destroy($id)
    {
        $installation = WordPressInstallation::findOrFail($id);

        $result = $this->installationService->uninstall($installation);

        if ($result['success']) {
            return redirect()
                ->route('wordpress.index')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    public function syncThemes($id)
    {
        $installation = WordPressInstallation::findOrFail($id);

        $result = $this->wpCLIService->syncThemes($installation);

        if ($result['success']) {
            return back()->with('success', "Synced {$result['count']} themes");
        }

        return back()->withErrors(['error' => 'Failed to sync themes']);
    }

    public function syncPlugins($id)
    {
        $installation = WordPressInstallation::findOrFail($id);

        $result = $this->wpCLIService->syncPlugins($installation);

        if ($result['success']) {
            return back()->with('success', "Synced {$result['count']} plugins");
        }

        return back()->withErrors(['error' => 'Failed to sync plugins']);
    }

    public function checkUpdates($id)
    {
        $installation = WordPressInstallation::findOrFail($id);

        // Sync themes and plugins to get latest update info
        $this->wpCLIService->syncThemes($installation);
        $this->wpCLIService->syncPlugins($installation);

        // Check core updates
        $coreResult = $this->wpCLIService->checkCoreUpdate($installation);

        $message = 'Update check completed';
        if ($coreResult['has_update'] ?? false) {
            $message .= ". WordPress {$coreResult['new_version']} is available";
        }

        return back()->with('success', $message);
    }
}
