<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Alias;
use App\Models\Domain;
use App\Models\Site;
use App\Services\DaemonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AliasController extends Controller
{
    public function index(string $site_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        if (! $site) {
            return response()->json([
                'message' => __('spikster.site_not_found_message'),
                'errors' => __('spikster.site_not_found'),
            ], 404);
        }

        $response = [];
        foreach ($site->aliases as $alias) {
            $response[] = [
                'alias_id' => $alias->alias_id,
                'domain' => $alias->domain,
            ];
        }

        return response()->json($response);
    }

    public function store(Request $request, string $site_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        if (! $site) {
            return response()->json([
                'message' => __('spikster.site_not_found_message'),
                'errors' => __('spikster.site_not_found'),
            ], 404);
        }

        $validator = Validator::make($request->all(), ['domain' => 'required']);
        if ($validator->fails()) {
            return response()->json([
                'message' => __('spikster.bad_request'),
                'errors' => $validator->errors()->getMessages(),
            ], 400);
        }

        $domain = strtolower($request->domain);

        $conflict = false;
        foreach ($site->server->allsites()->with('aliases')->get() as $checksite) {
            if ($checksite->domain === $domain) {
                $conflict = true;
            }
            foreach ($checksite->aliases as $alias) {
                if ($alias->domain === $domain) {
                    $conflict = true;
                }
            }
        }

        if ($conflict) {
            return response()->json([
                'message' => __('spikster.site_domain_conflict_message'),
                'errors' => __('spikster.site_domain_conflict'),
            ], 409);
        }

        $aliasId = Str::uuid();

        $alias = new Alias;
        $alias->alias_id = $aliasId;
        $alias->site_id = $site->id;
        $alias->domain = $domain;
        $alias->save();

        Domain::create([
            'domain_id' => $aliasId,
            'site_id' => $site->site_id,
            'server_id' => $site->server->server_id,
            'domain' => $domain,
            'is_primary' => false,
        ]);

        app(DaemonService::class)->createAlias(
            $alias->domain,
            $site->username,
            $site->php,
            $site->basepath ?? ''
        );

        return response()->json([
            'alias_id' => $alias->alias_id,
            'domain' => $alias->domain,
        ]);
    }

    public function destroy(string $site_id, string $alias_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        if (! $site) {
            return response()->json([
                'message' => __('spikster.site_not_found_message'),
                'errors' => __('spikster.site_not_found'),
            ], 404);
        }

        $alias = Alias::where('alias_id', $alias_id)->first();

        if (! $alias) {
            return response()->json([
                'message' => __('spikster.alias_not_found_message'),
                'errors' => __('spikster.alias_not_found'),
            ], 404);
        }

        app(DaemonService::class)->deleteAlias($alias->domain);
        Domain::where('domain_id', $alias_id)->delete();
        $alias->delete();

        return response()->json([]);
    }
}
