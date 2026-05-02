<?php

namespace App\Services;

use App\Models\Server;

class CloudInitService
{
    public function generate(Server $server, array $options = []): string
    {
        $sshKey = $options['ssh_key'] ?? file_get_contents('/etc/spikster/ssh_key.pub') ?? '';

        $yaml = "#cloud-config\n";
        $yaml .= "hostname: {$server->name}\n";
        $yaml .= "manage_etc_hosts: true\n\n";
        $yaml .= "users:\n";
        $yaml .= "  - name: spikster\n";
        $yaml .= "    sudo: ALL=(ALL) NOPASSWD:ALL\n";
        $yaml .= "    shell: /bin/bash\n";
        $yaml .= "    ssh_authorized_keys:\n";
        $yaml .= "      - {$sshKey}\n\n";
        $yaml .= "packages:\n";
        $yaml .= "  - curl\n";
        $yaml .= "  - wget\n";
        $yaml .= "  - git\n";
        $yaml .= "  - ufw\n\n";
        $yaml .= "package_update: true\n";
        $yaml .= "package_upgrade: {$options['upgrade_packages']}\n\n";
        $yaml .= "runcmd:\n";
        $yaml .= "  - ufw allow 22/tcp\n";
        $yaml .= "  - ufw allow 80/tcp\n";
        $yaml .= "  - ufw allow 443/tcp\n";
        $yaml .= "  - ufw --force enable\n";
        $yaml .= "  - curl -fsSL https://raw.githubusercontent.com/yolanmees/Spikster/main/go.sh | bash\n";

        return $yaml;
    }

    public function validate(string $yaml): array
    {
        $errors = [];

        if (! str_contains($yaml, '#cloud-config')) {
            $errors[] = 'Must start with #cloud-config header';
        }

        if (! preg_match('/users:\s*-\s*name:\s*\S+/', $yaml)) {
            $errors[] = 'Must define at least one user';
        }

        if (! preg_match('/ssh_authorized_keys:\s*-\s*ssh-\S+/', $yaml)) {
            $errors[] = 'Must include at least one SSH authorized key';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
