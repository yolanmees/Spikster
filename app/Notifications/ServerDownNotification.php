<?php

namespace App\Notifications;

use App\Models\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServerDownNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Server $server,
        public ?string $reason = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject("Server Down: {$this->server->name}")
            ->line('Your server is no longer reachable.')
            ->line('**Server:** ' . $this->server->name . ' (' . $this->server->ip . ')')
            ->when($this->reason, fn ($mail) => $mail->line('**Reason:** ' . $this->reason))
            ->line('**Detected At:** ' . now()->format('Y-m-d H:i:s'))
            ->action('View Server', url('/servers/' . $this->server->server_id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'server_id'   => $this->server->id,
            'server_name' => $this->server->name,
            'server_ip'   => $this->server->ip,
            'reason'      => $this->reason,
            'detected_at' => now(),
        ];
    }
}
