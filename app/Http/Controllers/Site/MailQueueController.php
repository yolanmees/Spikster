<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\RemoteDaemonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MailQueueController extends Controller
{
    public function __construct(private RemoteDaemonService $daemon) {}

    /**
     * List messages currently in the mail queue for this site's server.
     *
     * Returns an array of queue entries from `postqueue -j` output,
     * filtered to messages involving the site's domain (if any).
     *
     * GET /sites/{site_id}/email/queue
     */
    public function index(string $site_id): JsonResponse
    {
        $site = Site::where('site_id', $site_id)->with('server')->firstOrFail();

        $result = $this->daemon->send($site->server, 'email.queue-list', [
            'domain' => $site->domain,
        ]);

        return response()->json([
            'site_id' => $site_id,
            'domain' => $site->domain,
            'queue' => $result['queue'] ?? [],
            'total' => count($result['queue'] ?? []),
        ]);
    }

    /**
     * Retry (flush) a specific message in the mail queue.
     *
     * POST /sites/{site_id}/email/queue/{queue_id}/retry
     */
    public function retry(string $site_id, string $queue_id): JsonResponse
    {
        $site = Site::where('site_id', $site_id)->with('server')->firstOrFail();

        // Sanitize queue_id: postfix queue IDs are alphanumeric
        if (! preg_match('/^[A-F0-9]+$/i', $queue_id)) {
            return response()->json(['message' => 'Invalid queue ID format.'], 422);
        }

        $result = $this->daemon->send($site->server, 'email.queue-retry', [
            'queue_id' => $queue_id,
        ]);

        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? '',
        ]);
    }

    /**
     * Delete a specific message from the mail queue.
     *
     * DELETE /sites/{site_id}/email/queue/{queue_id}
     */
    public function destroy(string $site_id, string $queue_id): JsonResponse
    {
        $site = Site::where('site_id', $site_id)->with('server')->firstOrFail();

        if (! preg_match('/^[A-F0-9]+$/i', $queue_id)) {
            return response()->json(['message' => 'Invalid queue ID format.'], 422);
        }

        $result = $this->daemon->send($site->server, 'email.queue-delete', [
            'queue_id' => $queue_id,
        ]);

        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? '',
        ]);
    }

    /**
     * Retrieve recent mail log lines for the site's domain.
     *
     * GET /sites/{site_id}/email/logs
     *
     * Query params:
     *   - lines (int, default 100, max 1000): number of recent log lines
     *   - filter (string, optional): substring to filter by
     */
    public function logs(Request $request, string $site_id): JsonResponse
    {
        $site = Site::where('site_id', $site_id)->with('server')->firstOrFail();

        $lines = min((int) ($request->query('lines', 100)), 1000);
        $filter = $request->query('filter', '');

        // Sanitize filter to prevent log-injection artefacts being echoed back
        $filter = mb_substr(preg_replace('/[^\w\s@.\-]/', '', (string) $filter), 0, 128);

        $result = $this->daemon->send($site->server, 'email.log-tail', [
            'domain' => $site->domain,
            'lines' => $lines,
            'filter' => $filter,
        ]);

        return response()->json([
            'site_id' => $site_id,
            'domain' => $site->domain,
            'lines' => $result['lines'] ?? [],
            'count' => count($result['lines'] ?? []),
        ]);
    }
}
