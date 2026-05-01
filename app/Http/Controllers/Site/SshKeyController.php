<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\SiteSshKey;
use App\Services\DaemonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SshKeyController extends Controller
{
    /**
     * List all SSH public keys for a site.
     */
    public function index(string $site_id): JsonResponse
    {
        $site = Site::where('site_id', $site_id)->firstOrFail();

        $keys = SiteSshKey::where('site_id', $site->site_id)
            ->select(['id', 'label', 'fingerprint', 'created_at'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($keys);
    }

    /**
     * Add a new SSH public key to a site.
     */
    public function store(Request $request, string $site_id): JsonResponse
    {
        $site = Site::where('site_id', $site_id)->firstOrFail();

        $request->validate([
            'label' => 'required|string|max:128',
            'public_key' => 'required|string|max:4096',
        ]);

        $publicKey = trim($request->public_key);

        // Validate key format (ssh-rsa, ecdsa-*, ssh-ed25519)
        if (! preg_match('/^(ssh-rsa|ssh-ed25519|ecdsa-sha2-nistp256|ecdsa-sha2-nistp384|ecdsa-sha2-nistp521)\s+[A-Za-z0-9+\/=]+/', $publicKey)) {
            return response()->json([
                'message' => 'Invalid SSH public key format.',
                'errors' => 'invalid_public_key',
            ], 422);
        }

        // Derive fingerprint
        $parts = explode(' ', $publicKey);
        $fingerprint = base64_encode(hash('sha256', base64_decode($parts[1] ?? ''), true));

        if (SiteSshKey::where('site_id', $site->site_id)->where('fingerprint', $fingerprint)->exists()) {
            return response()->json([
                'message' => 'This SSH key is already added to the site.',
                'errors' => 'duplicate_key',
            ], 409);
        }

        $key = SiteSshKey::create([
            'site_id' => $site->site_id,
            'label' => $request->label,
            'public_key' => $publicKey,
            'fingerprint' => $fingerprint,
        ]);

        // Push to server daemon → appends to site user's authorized_keys
        app(DaemonService::class)->send('site.ssh-key-add', [
            'username' => $site->username,
            'public_key' => $publicKey,
        ]);

        return response()->json([
            'id' => $key->id,
            'label' => $key->label,
            'fingerprint' => $key->fingerprint,
            'created_at' => $key->created_at,
        ], 201);
    }

    /**
     * Remove an SSH public key from a site.
     */
    public function destroy(string $site_id, int $key_id): JsonResponse
    {
        $site = Site::where('site_id', $site_id)->firstOrFail();

        $key = SiteSshKey::where('id', $key_id)
            ->where('site_id', $site->site_id)
            ->firstOrFail();

        // Remove from server daemon → strips line from authorized_keys
        app(DaemonService::class)->send('site.ssh-key-remove', [
            'username' => $site->username,
            'fingerprint' => $key->fingerprint,
        ]);

        $key->delete();

        return response()->json(['message' => 'SSH key removed.']);
    }
}
