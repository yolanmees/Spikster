<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\DaemonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WildcardSslController extends Controller
{
    public function __construct(
        protected DaemonService $daemonService
    ) {}

    public function issueWildcard(Request $request, string $siteId): JsonResponse
    {
        $site = Site::where('site_id', $siteId)->firstOrFail();
        $this->authorize('manageSsl', $site);

        $validator = Validator::make($request->all(), [
            'domain' => 'required|string',
            'method' => 'required|in:dns-01,http-01',
            'dns_provider' => 'required_if:method,dns-01|string',
            'api_token' => 'required_if:method,dns-01|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $domain = $request->input('domain');
        $method = $request->input('method');

        if ($method === 'dns-01') {
            $result = $this->daemonService->send('site.wildcard-ssl-dns', [
                'username' => $site->username,
                'domain' => $domain,
                'wildcard_domain' => "*.".$domain,
                'dns_provider' => $request->input('dns_provider'),
                'api_token' => $request->input('api_token'),
            ]);
        } else {
            $result = $this->daemonService->enableSSL($site->username, $domain);
        }

        return response()->json([
            'message' => 'Wildcard SSL certificate request initiated',
            'domain' => $domain,
            'method' => $method,
            'success' => $result,
        ]);
    }
}
