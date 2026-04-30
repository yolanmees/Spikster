<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Alias;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AliasController extends Controller
{
    /**
     * List all site aliases
     */
    public function index(string $site_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        if (!$site) {
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

    /**
     * Add an alias to site
     */
    public function store(Request $request, string $site_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        if (!$site) {
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

        $conflict = false;
        foreach ($site->server->allsites as $checksite) {
            if ($checksite->domain == strtolower($request->domain)) {
                $conflict = true;
            }
            foreach ($checksite->aliases as $alias) {
                if ($alias->domain == strtolower($request->domain)) {
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

        $alias = new Alias;
        $alias->alias_id = Str::uuid();
        $alias->site_id = $site->id;
        $alias->domain = strtolower($request->domain);
        $alias->save();

        app(\App\Services\DaemonService::class)->createAlias(
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

    /**
     * Delete an alias
     */
    public function destroy(string $site_id, string $alias_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        if (!$site) {
            return response()->json([
                'message' => __('spikster.site_not_found_message'),
                'errors' => __('spikster.site_not_found'),
            ], 404);
        }

        $alias = Alias::where('alias_id', $alias_id)->first();

        if (!$alias) {
            return response()->json([
                'message' => __('spikster.alias_not_found_message'),
                'errors' => __('spikster.alias_not_found'),
            ], 404);
        }

        app(\App\Services\DaemonService::class)->deleteAlias($alias->domain);
        $alias->delete();

        return response()->json([]);
    }
}
