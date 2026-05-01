<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\DeploySite;
use App\Models\Deployment;
use App\Models\Site;
use App\Services\DeploymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeployController extends Controller
{
    public function __construct(
        protected DeploymentService $deploymentService
    ) {}

    public function deploy(Request $request, string $siteId): JsonResponse
    {
        $site = Site::where('site_id', $siteId)->firstOrFail();

        if (! $site->hasRepository()) {
            return response()->json([
                'success' => false,
                'message' => 'Site does not have a repository configured',
            ], 422);
        }

        $deployment = Deployment::create([
            'site_id' => $site->site_id,
            'server_id' => $site->server_id,
            'status' => 'pending',
            'branch' => $site->branch,
            'triggered_by' => 'user',
            'user_id' => $request->user()?->id,
        ]);

        DeploySite::dispatch($deployment);

        return response()->json([
            'success' => true,
            'message' => 'Deployment queued',
            'deployment' => [
                'id' => $deployment->id,
                'status' => $deployment->status,
            ],
        ]);
    }

    public function history(string $siteId): JsonResponse
    {
        $site = Site::where('site_id', $siteId)->firstOrFail();

        $deployments = Deployment::where('site_id', $site->site_id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn (Deployment $d) => [
                'id' => $d->id,
                'status' => $d->status,
                'branch' => $d->branch,
                'commit_hash' => $d->commit_hash,
                'commit_message' => $d->commit_message,
                'error_message' => $d->error_message,
                'steps' => $d->steps,
                'duration' => $d->getFormattedDuration(),
                'triggered_by' => $d->triggered_by,
                'started_at' => $d->started_at?->diffForHumans(),
                'completed_at' => $d->completed_at?->diffForHumans(),
                'created_at' => $d->created_at->diffForHumans(),
            ]);

        return response()->json([
            'success' => true,
            'deployments' => $deployments,
        ]);
    }

    public function rollback(Request $request, string $siteId, Deployment $deployment): JsonResponse
    {
        $site = Site::where('site_id', $siteId)->firstOrFail();

        if (! $deployment->isSuccessful()) {
            return response()->json([
                'success' => false,
                'message' => 'Can only rollback to a successful deployment',
            ], 422);
        }

        if (! $deployment->commit_hash) {
            return response()->json([
                'success' => false,
                'message' => 'Deployment has no commit hash to rollback to',
            ], 422);
        }

        try {
            $this->deploymentService->rollback($site, $deployment->commit_hash);

            $rollback = Deployment::create([
                'site_id' => $site->site_id,
                'server_id' => $site->server_id,
                'status' => 'pending',
                'branch' => $site->branch,
                'commit_hash' => $deployment->commit_hash,
                'triggered_by' => 'user',
                'user_id' => $request->user()?->id,
            ]);

            DeploySite::dispatch($rollback);

            return response()->json([
                'success' => true,
                'message' => 'Rollback queued',
                'deployment' => [
                    'id' => $rollback->id,
                    'status' => $rollback->status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function gitHistory(string $siteId): JsonResponse
    {
        $site = Site::where('site_id', $siteId)->firstOrFail();

        try {
            $commits = $this->deploymentService->getDeploymentHistory($site);

            return response()->json([
                'success' => true,
                'commits' => $commits,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
