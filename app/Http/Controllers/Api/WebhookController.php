<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(
        protected WebhookService $webhookService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $webhooks = Webhook::where('user_id', $request->user()->id)
            ->withCount('deliveries')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (Webhook $w) => [
                'id' => $w->id,
                'name' => $w->name,
                'url' => $w->url,
                'events' => $w->events,
                'is_active' => $w->is_active,
                'last_sent_at' => $w->last_sent_at?->diffForHumans(),
                'last_response_code' => $w->last_response_code,
                'failure_count' => $w->failure_count,
                'deliveries_count' => $w->deliveries_count,
                'created_at' => $w->created_at->diffForHumans(),
            ]);

        return response()->json([
            'success' => true,
            'webhooks' => $webhooks,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:1024',
            'secret' => 'nullable|string|max:255',
            'events' => 'required|array|min:1',
            'events.*' => 'string|in:'.implode(',', WebhookService::EVENTS),
        ]);

        $webhook = Webhook::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'url' => $validated['url'],
            'secret' => $validated['secret'],
            'events' => $validated['events'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Webhook created',
            'webhook' => ['id' => $webhook->id],
        ], 201);
    }

    public function show(Request $request, Webhook $webhook): JsonResponse
    {
        if ($webhook->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $deliveries = $webhook->deliveries()
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'event' => $d->event,
                'status' => $d->status,
                'response_code' => $d->response_code,
                'error_message' => $d->error_message,
                'created_at' => $d->created_at->diffForHumans(),
            ]);

        return response()->json([
            'success' => true,
            'webhook' => [
                'id' => $webhook->id,
                'name' => $webhook->name,
                'url' => $webhook->url,
                'events' => $webhook->events,
                'is_active' => $webhook->is_active,
                'last_sent_at' => $webhook->last_sent_at?->diffForHumans(),
                'last_response_code' => $webhook->last_response_code,
                'failure_count' => $webhook->failure_count,
            ],
            'deliveries' => $deliveries,
        ]);
    }

    public function update(Request $request, Webhook $webhook): JsonResponse
    {
        if ($webhook->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'url' => 'sometimes|url|max:1024',
            'secret' => 'nullable|string|max:255',
            'events' => 'sometimes|array|min:1',
            'events.*' => 'string|in:'.implode(',', WebhookService::EVENTS),
            'is_active' => 'sometimes|boolean',
        ]);

        $webhook->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Webhook updated',
        ]);
    }

    public function destroy(Request $request, Webhook $webhook): JsonResponse
    {
        if ($webhook->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $webhook->delete();

        return response()->json([
            'success' => true,
            'message' => 'Webhook deleted',
        ]);
    }

    public function test(Request $request, Webhook $webhook): JsonResponse
    {
        if ($webhook->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $delivery = $this->webhookService->test($webhook);

        return response()->json([
            'success' => $delivery->status === 'success',
            'message' => $delivery->status === 'success'
                ? 'Webhook test sent successfully'
                : 'Webhook test failed',
            'delivery' => [
                'status' => $delivery->status,
                'response_code' => $delivery->response_code,
                'error_message' => $delivery->error_message,
            ],
        ]);
    }

    public function events(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'events' => WebhookService::EVENTS,
        ]);
    }
}
