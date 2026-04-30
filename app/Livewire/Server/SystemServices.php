<?php

namespace App\Livewire\Server;

use App\Models\Server;
use App\Services\DaemonService;
use Livewire\Component;

class SystemServices extends Component
{
    public $server_id;
    public $services = [
        'nginx'      => ['name' => 'nginx',      'status' => 'unknown'],
        'php'        => ['name' => 'PHP-FPM',     'status' => 'unknown'],
        'mysql'      => ['name' => 'MySql',       'status' => 'unknown'],
        'redis'      => ['name' => 'Redis',       'status' => 'unknown'],
        'supervisor' => ['name' => 'Supervisor',  'status' => 'unknown'],
    ];
    public $restarting = [];

    private array $serviceMap = [
        'nginx'      => 'nginx',
        'php'        => ['php8.4-fpm', 'php8.3-fpm', 'php8.2-fpm'],
        'mysql'      => 'mysql',
        'redis'      => 'redis-server',
        'supervisor' => 'supervisor',
    ];

    public function mount($server_id): void
    {
        $this->server_id = $server_id;
    }

    public function restartService(string $service): void
    {
        if (!array_key_exists($service, $this->serviceMap)) {
            session()->flash('error', 'Unknown service.');
            return;
        }

        $server = Server::where('server_id', $this->server_id)->first();
        if (!$server) {
            session()->flash('error', 'Server not found.');
            return;
        }

        $this->restarting[$service] = true;

        try {
            $daemon   = app(DaemonService::class);
            $services = (array) $this->serviceMap[$service];

            foreach ($services as $svc) {
                $daemon->restart($svc);
            }

            session()->flash('success', ucfirst($service) . ' restarted.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to restart ' . $service . ': ' . $e->getMessage());
        } finally {
            unset($this->restarting[$service]);
        }
    }

    public function render()
    {
        return view('livewire.server.system-services');
    }
}
