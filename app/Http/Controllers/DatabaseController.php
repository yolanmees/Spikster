<?php

namespace App\Http\Controllers;

use App\Models\Database;
use App\Models\DatabaseUser;
use App\Services\DatabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DatabaseController extends Controller
{
    protected $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    public function viewdatabase($siteId): \Illuminate\View\View
    {
        $databases     = Database::with('users')->where('site_id', $siteId)->get();
        $databaseUsers = DatabaseUser::where('site_id', $siteId)->get();

        return view('site.database.index', compact('databases', 'databaseUsers', 'siteId'));
    }

    public function createdatabase(Request $request, $site_id): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'database_name' => ['required', 'string', 'max:64', 'regex:/^[a-zA-Z][a-zA-Z0-9_]{1,63}$/'],
        ]);

        $response = $this->databaseService->createDatabase($request->input('database_name'), $site_id);

        return $response['success']
            ? \Redirect::back()->with('success', $response['message'])
            : \Redirect::back()->with('failed', $response['message']);
    }

    public function createuser(Request $request, $site_id): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'max:64', 'regex:/^[a-zA-Z][a-zA-Z0-9_]{1,63}$/'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $response = $this->databaseService->createUser(
            $request->input('username'),
            $request->input('password'),
            $site_id
        );

        return $response['success']
            ? \Redirect::back()->with('success', $response['message'])
            : \Redirect::back()->with('failed', $response['message']);
    }

    public function linkdatabaseuser(Request $request, $site_id): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'integer'],
            'database' => ['required', 'integer'],
        ]);

        $response = $this->databaseService->linkDatabaseUser(
            $request->input('username'),
            $request->input('database'),
            $site_id
        );

        return $response['success']
            ? \Redirect::back()->with('success', $response['message'])
            : \Redirect::back()->with('failed', $response['message']);
    }

    public function deleteDatabase(Request $request, $site_id): \Illuminate\Http\RedirectResponse
    {
        $request->validate(['database_id' => ['required', 'integer']]);

        $response = $this->databaseService->deleteDatabase($request->input('database_id'));

        return $response['success']
            ? redirect()->back()->with('success', $response['message'])
            : redirect()->back()->with('failed', $response['message']);
    }

    public function deleteUser(Request $request, $site_id): \Illuminate\Http\RedirectResponse
    {
        $request->validate(['user_id' => ['required', 'integer']]);

        $response = $this->databaseService->deleteUser($request->input('user_id'));

        return $response['success']
            ? redirect()->back()->with('success', $response['message'])
            : redirect()->back()->with('failed', $response['message']);
    }

    /**
     * Named deleteLink to match the registered route (site.database.delete.link).
     */
    public function deleteLink(Request $request, $site_id): \Illuminate\Http\RedirectResponse
    {
        $request->validate(['link_id' => ['required', 'integer']]);

        $response = $this->databaseService->unlinkDatabaseUser($request->input('link_id'));

        return $response['success']
            ? redirect()->back()->with('success', $response['message'])
            : redirect()->back()->with('failed', $response['message']);
    }
}
