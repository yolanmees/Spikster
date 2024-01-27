<?php

namespace App\Http\Controllers;

use App\Models\Database;
use App\Models\DatabaseUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\DatabaseService;

class DatabaseController extends Controller
{
    protected $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    public function viewdatabase($siteId): \Illuminate\View\View
    {
        $databases = Database::with('users')->where(['site_id' => $siteId])->get();
        $databaseUsers = DatabaseUser::all();

        return view('site.database.index', compact('databases', 'databaseUsers', 'siteId'));
    }

    public function createdatabase(Request $request, $site_id): \Illuminate\Http\RedirectResponse
    {
        $response = $this->databaseService->createDatabase($request->input('database_name'), $site_id);

        if ($response['success']) {
            return \Redirect::back()->with('success', $response['message']);
        } else {
            return \Redirect::back()->with('failed', $response['message']);
        }
    }

    public function createuser(Request $request, $site_id): \Illuminate\Http\RedirectResponse
    {
        $response = $this->databaseService->createUser($request->input('username'), $request->input('password'), $site_id);

        if ($response['success']) {
            return \Redirect::back()->with('success', $response['message']);
        } else {
            return \Redirect::back()->with('failed', $response['message']);
        }
    }

    public function linkdatabaseuser(Request $request, $site_id): \Illuminate\Http\RedirectResponse
    {
        $response = $this->databaseService->linkDatabaseUser($request->input('username'), $request->input('database'), $site_id);

        if ($response['success']) {
            return \Redirect::back()->with('success', $response['message']);
        } else {
            return \Redirect::back()->with('failed', $response['message']);
        }
    }

    public function deleteDatabase(Request $request, $site_id)
    {
        $databaseId = $request->input('database_id');
        $response = $this->databaseService->deleteDatabase($databaseId);

        if ($response['success']) {
            return redirect()->back()->with('success', $response['message']);
        } else {
            return redirect()->back()->with('failed', $response['message']);
        }
    }

    public function deleteUser(Request $request, $site_id)
    {
        $userId = $request->input('user_id');
        $response = $this->databaseService->deleteUser($userId);

        if ($response['success']) {
            return redirect()->back()->with('success', $response['message']);
        } else {
            return redirect()->back()->with('failed', $response['message']);
        }
    }

    public function unlinkDatabaseUser(Request $request, $site_id)
    {
        $linkId = $request->input('link_id');
        $response = $this->databaseService->unlinkDatabaseUser($linkId);

        if ($response['success']) {
            return redirect()->back()->with('success', $response['message']);
        } else {
            return redirect()->back()->with('failed', $response['message']);
        }
    }
}