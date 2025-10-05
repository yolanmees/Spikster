<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Wordpress;
use App\Services\WordPressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WordPressController extends Controller
{
    protected $wordpressService;

    public function __construct(WordPressService $wordpressService)
    {
        $this->wordpressService = $wordpressService;
    }

    public function index($site_id): \Illuminate\View\View
    {
        // Verify site exists
        $site = Site::where('site_id', $site_id)->first();
        if (!$site) {
            abort(404, 'Site not found');
        }

        $wordpresses = Wordpress::with('database')->where('site_id', $site_id)->paginate(10);

        return view('site.wordpress.index', compact('site_id', 'site', 'wordpresses'));
    }

    public function create(Request $request, $site_id): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'path' => 'required|string',
            'username' => 'required|string|min:3|max:255',
            'password' => 'required|string|min:8',
        ]);

        $site = Site::where('site_id', $site_id)->first();
        if (!$site) {
            return back()->withErrors(['error' => 'Site not found. Please check the site ID.']);
        }

        $path = $site->rootpath . '/' . trim($request->input('path'), '/');

        try {
            $response = $this->wordpressService->deployWordPress(
                $path,
                $request->input('username'),
                $request->input('password'),
                $site_id
            );

            if ($response['success']) {
                return redirect()->route('site.wordpress', $site_id)
                    ->with('success', $response['message']);
            } else {
                return back()->withErrors(['error' => $response['message']]);
            }
        } catch (\Exception $e) {
            Log::error('WordPress deployment failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'WordPress deployment failed: ' . $e->getMessage()]);
        }
    }
}
