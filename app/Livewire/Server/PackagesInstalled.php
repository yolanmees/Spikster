<?php

namespace App\Livewire\Server;

use App\Models\Server;
use Livewire\Component;
use Illuminate\Support\Facades\Http;

class PackagesInstalled extends Component
{
    public $server_id;
    public $server;
    public $packages;
    public $installable_packages;

    public function render()
    {
        return view('livewire.server.packages-installed');
    }

    public function mount($server_id)
    {
        $this->server_id = $server_id;
        $this->server = Server::where(['server_id' => $server_id])->first();
        $this->installablePackages();
        $this->packages = $this->getPackages();
        // dd($this->packages);
    }

    public function getInstalledPackages()
    {
        $url = $this->server->ip . '/api/servers/' . $this->server->server_id . '/services';
        $response = Http::get($url);
        $response = $response->json()[0];
        foreach ($response as $key => $value) {
            if ($value['status'] !== "install") {
                unset($response[$key]);
            }
        }
        return $response;
    }

    public function getPackages()
    {
        $installed_packages = $this->getInstalledPackages();
        $installed = [];
        foreach ($installed_packages as $value) {
            $installed[] = $value['package'];
        }

        $packages = [];
        for ($i = 0; $i < count($this->installable_packages); $i++) {
            $package = $this->installable_packages[$i];
            $packages[$i]['package'] = $package;
            if (in_array($package, $installed)) {
                $packages[$i]['installed'] = "true";
            } else {
                $packages[$i]['installed'] = "false";
            }
        }

        return $packages;
    }

    public function install($package)
    {
        $url = $this->server->ip . '/api/servers/' . $this->server->server_id . '/packages/install/';
        $data = [
            'package' => $package
        ];
        $response = Http::post($url, $data);
        $response = $response->json();

        $this->mount($this->server_id);
    }

    public function uninstall($package)
    {
        $url = $this->server->ip . '/api/servers/' . $this->server->server_id . '/packages/uninstall/';
        $data = [
            'package' => $package
        ];
        $response = Http::post($url, $data);
        dd($response->json());
        $this->mount($this->server_id);
    }

    public function installablePackages()
    {
        $this->installable_packages = [
            "composer",
            "fail2ban",
            "glances",
            "nodejs",
            "php7.4-bcmath",
            "php7.4-cli",
            "php7.4-common",
            "php7.4-curl",
            "php7.4-fpm",
            "php7.4-gd",
            "php7.4-igbinary",
            "php7.4-imagick",
            "php7.4-imap",
            "php7.4-json",
            "php7.4-mbstring",
            "php7.4-memcached",
            "php7.4-msgpack",
            "php7.4-mysql",
            "php7.4-opcache",
            "php7.4-pgsql",
            "php7.4-readline",
            "php7.4-redis",
            "php7.4-soap",
            "php7.4-sqlite3",
            "php7.4-xml",
            "php7.4-zip",
            "php8.0-bcmath",
            "php8.0-cli",
            "php8.0-common",
            "php8.0-curl",
            "php8.0-fpm",
            "php8.0-gd",
            "php8.0-igbinary",
            "php8.0-imagick",
            "php8.0-imap",
            "php8.0-mbstring",
            "php8.0-memcached",
            "php8.0-msgpack",
            "php8.0-mysql",
            "php8.0-opcache",
            "php8.0-pgsql",
            "php8.0-readline",
            "php8.0-redis",
            "php8.0-soap",
            "php8.0-sqlite3",
            "php8.0-xml",
            "php8.0-zip",
            "php8.1-bcmath",
            "php8.1-cli",
            "php8.1-common",
            "php8.1-curl",
            "php8.1-fpm",
            "php8.1-gd",
            "php8.1-igbinary",
            "php8.1-imagick",
            "php8.1-imap",
            "php8.1-mbstring",
            "php8.1-memcached",
            "php8.1-msgpack",
            "php8.1-mysql",
            "php8.1-opcache",
            "php8.1-pgsql",
            "php8.1-readline",
            "php8.1-redis",
            "php8.1-soap",
            "php8.1-sqlite3",
            "php8.1-xml",
            "php8.1-zip",
            "php8.2-bcmath",
            "php8.2-bz2",
            "php8.2-cli",
            "php8.2-common",
            "php8.2-curl",
            "php8.2-dev",
            "php8.2-fpm",
            "php8.2-gd",
            "php8.2-igbinary",
            "php8.2-imagick",
            "php8.2-imap",
            "php8.2-mbstring",
            "php8.2-mcrypt",
            "php8.2-memcached",
            "php8.2-msgpack",
            "php8.2-mysql",
            "php8.2-opcache",
            "php8.2-pgsql",
            "php8.2-readline",
            "php8.2-redis",
            "php8.2-soap",
            "php8.2-sqlite3",
            "php8.2-xml",
            "php8.2-zip",
            "php8.3-bcmath",
            "php8.3-bz2",
            "php8.3-cli",
            "php8.3-common",
            "php8.3-curl",
            "php8.3-dev",
            "php8.3-fpm",
            "php8.3-gd",
            "php8.3-igbinary",
            "php8.3-imagick",
            "php8.3-imap",
            "php8.3-mbstring",
            "php8.3-mcrypt",
            "php8.3-memcached",
            "php8.3-msgpack",
            "php8.3-mysql",
            "php8.3-opcache",
            "php8.3-pgsql",
            "php8.3-readline",
            "php8.3-redis",
            "php8.3-soap",
            "php8.3-sqlite3",
            "php8.3-xml",
            "php8.3-zip",
            "phpmyadmin"
        ];
    }
}
