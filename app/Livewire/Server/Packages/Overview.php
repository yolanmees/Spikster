<?php

namespace App\Livewire\Server\Packages;

use App\Models\Server;
use Livewire\Component;
use Illuminate\Support\Facades\Http;

class Overview extends Component
{

    public $server;
    public $server_id;
    public $items;

    public function mount($server_id)
    {
        $this->server_id = $server_id;
        $this->server = Server::where('server_id', $server_id)->first();
        $this->items = $this->getPackages();
        $this->items['installed'] = $this->getInstalledPackages();

    }

    public function render()
    {
        return view('livewire.server.packages.overview');
    }

    private function getPackages()
    {
        return [
            'services' => [
                ['name' => 'spamassassin', 'description' => 'SpamAssassin is a mail filter which attempts to identify spam using a variety of mechanisms including text analysis, Bayesian filtering, DNS blocklists, and collaborative filtering databases.'],
                ['name' => 'nginx', 'description' => 'Nginx is a web server which can also be used as a reverse proxy, load balancer, mail proxy and HTTP cache.'],
                ['name' => 'exim4', 'description' => 'Exim is a message transfer agent (MTA) developed at the University of Cambridge for use on Unix systems connected to the Internet.'],
                ['name' => 'dovecot', 'description' => 'Dovecot is an open-source IMAP and POP3 server for Unix-like operating systems, written primarily with security in mind.'],
                ['name' => 'php8.3-fpm', 'description' => 'PHP-FPM (FastCGI Process Manager) is an alternative PHP FastCGI implementation with some additional features useful for sites of any size, especially busier sites.'],
                ['name' => 'clamav-daemon', 'description' => 'ClamAV is an open-source antivirus engine for detecting trojans, viruses, malware & other malicious threats.'],
                ['name' => 'redis-server', 'description' => 'Redis is an open-source, in-memory data structure store, used as a database, cache, and message broker.'],
                ['name' => 'mysql', 'description' => 'MySQL is an open-source relational database management system.'],
            ],
            'processes' => [
                ['name' => 'glances', 'description' => 'Glances is a cross-platform monitoring tool which aims to present a large amount of monitoring information through a curses or Web-based interface.'],
            ],
            'packages' => [
                ['name' => 'roundcube', 'description' => 'Roundcube is a browser-based multilingual IMAP client with an application-like user interface.'],
            ],
        ];
    }

    public function getInstalledPackages()
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . session('token'),
        ])->get($this->server->ip. '/servers/' . $this->server_id . '/packages');

        return $response->json();

    }

    public function getInstalledPackagesMock()
    {
        return [
            'services' => [
                ['name' => 'spamassassin', 'status' => 'ACTIVE'],
                ['name' => 'nginx', 'status' => 'ACTIVE'],
                ['name' => 'exim4', 'status' => 'ACTIVE'],
                ['name' => 'dovecot', 'status' => 'ACTIVE'],
                ['name' => 'php8.3-fpm', 'status' => 'ACTIVE'],
                ['name' => 'clamav-daemon', 'status' => 'INACTIVE'],
                ['name' => 'redis-server', 'status' => 'ACTIVE'],
                ['name' => 'mysql', 'status' => 'ACTIVE'],
            ],
            'processes' => [
                ['name' => 'glances', 'status' => 'ACTIVE'],
            ],
            'packages' => [
                ['name' => 'roundcube', 'status' => 'INSTALLED'],
            ],
        ];
    }
}
