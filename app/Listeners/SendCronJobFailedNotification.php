<?php

namespace App\Listeners;

use App\Events\CronJobFailed;
use App\Models\User;
use App\Notifications\CronJobFailedNotification;
use Illuminate\Support\Facades\Log;

class SendCronJobFailedNotification
{
    /**
     * Handle the event.
     */
    public function handle(CronJobFailed $event): void
    {
        $execution = $event->execution;
        $cronJob = $execution->cronJob;
        $server = $cronJob->server;

        // Log the failure
        Log::error('Cron job failed', [
            'cron_job_id' => $cronJob->id,
            'execution_id' => $execution->id,
            'command' => $cronJob->command,
            'exit_code' => $execution->exit_code,
            'error' => $execution->error_output,
        ]);

        // Send notification to admin users
        $admins = User::role('admin')->get();
        
        foreach ($admins as $admin) {
            $admin->notify(new CronJobFailedNotification($execution));
        }

        // Could also send to specific users configured for this cron job
        // if ($cronJob->notification_emails) {
        //     $emails = explode(',', $cronJob->notification_emails);
        //     foreach ($emails as $email) {
        //         Notification::route('mail', $email)
        //             ->notify(new CronJobFailedNotification($execution));
        //     }
        // }
    }
}
