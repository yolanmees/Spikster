<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class CredentialController extends Controller
{
    /**
     * Reset site SSH password
     */
    public function resetSsh(string $site_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        if (!$site) {
            return response()->json([
                'message' => __('spikster.site_not_found_message'),
                'errors' => __('spikster.site_not_found'),
            ], 404);
        }

        $newpassword = Str::random(24);
        $site->password = $newpassword;
        $site->save();

        app(\App\Services\DaemonService::class)->send('site.user-password', [
            'username' => $site->username,
            'password' => $newpassword,
        ]);

        $pdftoken = JWT::encode(
            ['iat' => time(), 'exp' => time() + 180],
            config('cipi.jwt_secret') . '-Pdf',
            'HS256'
        );

        return response()->json([
            'password' => $site->password,
            'pdf' => URL::to('/pdf/' . $site->site_id . '/' . $pdftoken),
        ]);
    }

    /**
     * Reset site MySQL password
     */
    public function resetDb(string $site_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        if (!$site) {
            return response()->json([
                'message' => __('spikster.site_not_found_message'),
                'errors' => __('spikster.site_not_found'),
            ], 404);
        }

        $last_password = $site->database;
        $site->database = Str::random(24);
        $site->save();

        app(\App\Services\DaemonService::class)->send('site.db-password', [
            'username' => $site->username,
            'old_pass' => $last_password,
            'new_pass' => $site->database,
        ]);

        $pdftoken = JWT::encode(
            ['iat' => time(), 'exp' => time() + 180],
            config('cipi.jwt_secret') . '-Pdf',
            'HS256'
        );

        return response()->json([
            'password' => $site->database,
            'pdf' => URL::to('/pdf/' . $site->site_id . '/' . $pdftoken),
        ]);
    }

    /**
     * Download site credentials PDF (time-limited token)
     */
    public function pdf(string $site_id, string $pdftoken)
    {
        try {
            JWT::decode($pdftoken, new Key(config('cipi.jwt_secret') . '-Pdf', 'HS256'));
        } catch (\Throwable $th) {
            abort(403);
        }

        $site = Site::where('site_id', $site_id)->firstOrFail();

        $data = [
            'username' => $site->username,
            'password' => $site->password,
            'path' => $site->basepath,
            'ip' => $site->server->ip,
            'domain' => $site->domain,
            'dbpass' => $site->database,
            'php' => $site->php,
        ];

        $pdf = PDF::loadView('pdf', $data);

        return $pdf->download($site->username . '_' . date('YmdHi') . '_' . date('s') . '.pdf');
    }

    /**
     * Auto-login to phpMyAdmin
     */
    public function autoLoginPMA(string $site_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        if (!$site) {
            return back();
        }

        return redirect()->to('mysecureadmin/index.php?username=' . $site->username . '&password=' . $site->database);
    }
}
