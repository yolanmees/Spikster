<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\WordPressService;
use Illuminate\Http\Request;
use App\Models\Wordpress;
use App\Models\Site;

class WordPressController extends Controller
{
    protected $wordpressService;

    public function __construct(WordPressService $wordpressService)
    {
        $this->wordpressService = $wordpressService;
    }

    public function index($site_id): \Illuminate\View\View
    {
        $wordpresses = Wordpress::with('database')->where(['site_id' => $site_id])->paginate(10);
        return view('site.wordpress.index', compact('site_id', 'wordpresses'));
    }

    public function create(Request $request, $site_id): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'path'     => 'required|string',
            'username' => 'required|string|min:3|max:255',
            'password' => 'required|string|min:8',
        ]);

        $site = Site::where(['site_id' => $site_id])->first();
        if (!$site) {
            return back()->withErrors(['Site not found']);
        }

        $path = $site->rootpath . '/' . $request->input('path');

        $response = $this->wordpressService->deployWordPress(
            $path,
            $request->input('username'),
            $request->input('password'),
            $site_id
        );

        if ($response['success']) {
            return redirect()->route('site.wordpress', $site_id)->with('success', $response['message']);
        } else {
            return back()->withErrors(['Deployment failed' => $response['message']]);
        }
    }
}