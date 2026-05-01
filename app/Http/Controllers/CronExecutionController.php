<?php

namespace App\Http\Controllers;

use App\Events\CronJobFailed;
use App\Models\CronJob;
use App\Models\CronJobExecution;
use Illuminate\Http\Request;

class CronExecutionController extends Controller
{
    /**
     * Get all executions for a cron job
     */
    public function index(CronJob $cronJob)
    {
        $executions = $cronJob->executions()
            ->orderBy('started_at', 'desc')
            ->paginate(50);

        return response()->json($executions);
    }

    /**
     * Store a new execution record
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cron_job_id' => 'required|exists:cron_jobs,id',
            'started_at' => 'required|date',
        ]);

        $execution = CronJobExecution::create([
            'cron_job_id' => $validated['cron_job_id'],
            'started_at' => $validated['started_at'],
            'status' => 'running',
        ]);

        return response()->json($execution, 201);
    }

    /**
     * Update an execution record
     */
    public function update(Request $request, CronJobExecution $execution)
    {
        $validated = $request->validate([
            'finished_at' => 'nullable|date',
            'duration' => 'nullable|integer',
            'status' => 'nullable|in:running,success,failed,timeout',
            'exit_code' => 'nullable|integer',
            'output' => 'nullable|string',
            'error_output' => 'nullable|string',
        ]);

        $execution->update($validated);

        // Check if we need to send notifications
        if ($execution->isFailed() && $execution->cronJob->notify_on_error) {
            // TODO: Send notification
            event(new CronJobFailed($execution));
        }

        return response()->json($execution);
    }

    /**
     * Get a single execution
     */
    public function show(CronJobExecution $execution)
    {
        return response()->json($execution->load('cronJob'));
    }

    /**
     * Delete an execution
     */
    public function destroy(CronJobExecution $execution)
    {
        $execution->delete();

        return response()->json(['message' => 'Execution deleted successfully']);
    }

    /**
     * Clean up old executions
     */
    public function cleanup(Request $request)
    {
        $days = $request->input('days', 30);

        $deleted = CronJobExecution::where('started_at', '<', now()->subDays($days))
            ->delete();

        return response()->json([
            'message' => "Deleted {$deleted} old execution records",
            'deleted' => $deleted,
        ]);
    }
}
