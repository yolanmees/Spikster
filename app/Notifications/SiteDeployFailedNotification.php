<?php

namespace App\Notifications;

use App\Models\Site;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SiteDeployFailedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Site $site,
        public ?string $errorOutput = null,
        public ?string $deployBranch = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $server = $this->site->server;

        return (new MailMessage)
            ->error()
            ->subject("Deploy Failed: {$this->site->domain}")
            ->line('A deployment has failed for your site.')
            ->line('**Server:** ' . $server->name . ' (' . $server->ip . ')')
            ->line('**Site:** ' . $this->site->domain)
            ->when($this->deployBranch, fn ($mail) => $mail->line('**Branch:** ' . $this->deployBranch))
            ->when($this->errorOutput, function ($mail) {
                return $mail->line('**Error Output:**')
                    ->line('```')
                    ->line(substr($this->errorOutput, 0, 500))
                    ->line('```');
            })
            ->line('**Failed At:** ' . now()->format('Y-m-d H:i:s'))
            ->action('View Site', url('/servers/' . $server->server_id . '/sites/' . $this->site->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'site_id'       => $this->site->id,
            'domain'        => $this->site->domain,
            'server_id'     => $this->site->server_id,
            'deploy_branch' => $this->deployBranch,
            'error_output'  => $this->errorOutput ? substr($this->errorOutput, 0, 500) : null,
            'failed_at'     => now(),
        ];
    }
}
