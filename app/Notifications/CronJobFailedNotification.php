<?php

namespace App\Notifications;

use App\Models\CronJobExecution;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CronJobFailedNotification extends Notification
{
    use Queueable;

    public CronJobExecution $execution;

    /**
     * Create a new notification instance.
     */
    public function __construct(CronJobExecution $execution)
    {
        $this->execution = $execution;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $cronJob = $this->execution->cronJob;
        $server = $cronJob->server;

        return (new MailMessage)
            ->error()
            ->subject('Cron Job Failed: ' . ($cronJob->description ?: $cronJob->command))
            ->line('A scheduled cron job has failed on your server.')
            ->line('**Server:** ' . $server->name . ' (' . $server->ip . ')')
            ->line('**Description:** ' . ($cronJob->description ?: 'No description'))
            ->line('**Command:** `' . $cronJob->command . '`')
            ->line('**Exit Code:** ' . $this->execution->exit_code)
            ->line('**Duration:** ' . $this->execution->formatted_duration)
            ->line('**Started:** ' . $this->execution->started_at->format('Y-m-d H:i:s'))
            ->when($this->execution->error_output, function ($mail) {
                return $mail->line('**Error Output:**')
                    ->line('```')
                    ->line(substr($this->execution->error_output, 0, 500))
                    ->line('```');
            })
            ->action('View Execution Details', url('/servers/' . $server->server_id . '/cron'));
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'execution_id' => $this->execution->id,
            'cron_job_id' => $this->execution->cron_job_id,
            'server_id' => $this->execution->cronJob->server_id,
            'command' => $this->execution->cronJob->command,
            'exit_code' => $this->execution->exit_code,
            'started_at' => $this->execution->started_at,
        ];
    }
}
