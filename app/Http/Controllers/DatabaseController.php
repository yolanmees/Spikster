<?php

namespace App\Http\Controllers;

use App\Models\Database;
use App\Models\DatabaseUser;
use App\Services\DatabaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DatabaseController extends Controller
{
    protected DatabaseService $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    public function index($siteId): View
    {
        $databases = Database::with('users')->where('site_id', $siteId)->get();
        $databaseUsers = DatabaseUser::where('site_id', $siteId)->get();

        return view('site.database.index', compact('databases', 'databaseUsers', 'siteId'));
    }

    public function createDatabase(Request $request, $siteId): RedirectResponse
    {
        $request->validate([
            'database_name' => ['required', 'string', 'max:64', 'regex:/^[a-zA-Z][a-zA-Z0-9_]{1,63}$/'],
        ]);

        $response = $this->databaseService->createDatabase($request->input('database_name'), $siteId);

        return $response['success']
            ? redirect()->back()->with('success', $response['message'])
            : redirect()->back()->with('failed', $response['message']);
    }

    public function createUser(Request $request, $siteId): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'max:64', 'regex:/^[a-zA-Z][a-zA-Z0-9_]{1,63}$/'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $response = $this->databaseService->createUser(
            $request->input('username'),
            $request->input('password'),
            $siteId
        );

        return $response['success']
            ? redirect()->back()->with('success', $response['message'])
            : redirect()->back()->with('failed', $response['message']);
    }

    public function linkDatabaseUser(Request $request, $siteId): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'integer'],
            'database' => ['required', 'integer'],
        ]);

        $response = $this->databaseService->linkDatabaseUser(
            $request->input('username'),
            $request->input('database'),
            $siteId
        );

        return $response['success']
            ? redirect()->back()->with('success', $response['message'])
            : redirect()->back()->with('failed', $response['message']);
    }

    public function deleteDatabase(Request $request, $siteId): RedirectResponse
    {
        $request->validate(['database_id' => ['required', 'integer']]);

        $response = $this->databaseService->deleteDatabase($request->input('database_id'), $siteId);

        return $response['success']
            ? redirect()->back()->with('success', $response['message'])
            : redirect()->back()->with('failed', $response['message']);
    }

    public function deleteUser(Request $request, $siteId): RedirectResponse
    {
        $request->validate(['user_id' => ['required', 'integer']]);

        $response = $this->databaseService->deleteUser($request->input('user_id'), $siteId);

        return $response['success']
            ? redirect()->back()->with('success', $response['message'])
            : redirect()->back()->with('failed', $response['message']);
    }

    public function deleteLink(Request $request, $siteId): RedirectResponse
    {
        $request->validate(['link_id' => ['required', 'integer']]);

        $response = $this->databaseService->unlinkDatabaseUser($request->input('link_id'), $siteId);

        return $response['success']
            ? redirect()->back()->with('success', $response['message'])
            : redirect()->back()->with('failed', $response['message']);
    }
}
