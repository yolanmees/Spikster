<?php

namespace App\Notifications;

use App\Models\Backup;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BackupFailedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Backup $backup
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $site = $this->backup->site;
        $server = $site->server;

        return (new MailMessage)
            ->error()
            ->subject("Backup Failed: {$site->domain}")
            ->line('A backup job has failed for your site.')
            ->line('**Server:** '.$server->name.' ('.$server->ip.')')
            ->line('**Site:** '.$site->domain)
            ->line('**Backup Type:** '.ucfirst($this->backup->type ?? 'full'))
            ->line('**Error:** '.($this->backup->failure_reason ?? 'Unknown error'))
            ->line('**Started:** '.optional($this->backup->started_at)->format('Y-m-d H:i:s'))
            ->action('View Backups', url('/servers/'.$server->server_id.'/backups'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'backup_id' => $this->backup->id,
            'site_id' => $this->backup->site_id,
            'server_id' => $this->backup->site->server_id ?? null,
            'domain' => $this->backup->site->domain,
            'type' => $this->backup->type,
            'failure_reason' => $this->backup->failure_reason,
            'started_at' => $this->backup->started_at,
        ];
    }
}
