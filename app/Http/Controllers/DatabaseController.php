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
}