<?php

namespace App\Services;

class DnsService
{
    public function __construct(
        private string $namedConf = '/etc/bind/named.conf.local'
    ) {}

    public function addZone(
        string $zone,
        string $email,
        array $nameservers
    ): string {
        if (! $zone || ! $email || ! $nameservers) {
            throw new \Exception('Missing required parameter: zone, email or nameservers');
        }
        $zone = htmlspecialchars($zone, ENT_QUOTES, 'utf-8');
        $email = htmlspecialchars($email, ENT_QUOTES, 'utf-8');
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = str_replace('@', '.', $email);
        } else {
            throw new \Exception('Invalid email address');
        }
        foreach ($nameservers as $key => $value) {
            $nameservers[$key] = htmlspecialchars($value, ENT_QUOTES, 'utf-8');
        }

        $zoneFile = '/root/zones/'.$zone;
        $template = '$TTL    86400
        @       IN      SOA     '.$nameservers[0].'. '.$email.' (
                                '.time().' ; serial
                                3600       ; refresh
                                1800       ; retry
                                604800     ; expire
                                86400 )    ; minimum
        ';
        $validNameservers = [];
        foreach ($nameservers as $i => $nameserver) {
            if (filter_var($nameserver, FILTER_VALIDATE_DOMAIN)) {
                $validNameservers[] = $nameserver;
            }
        }

        if (count($validNameservers) < 2 || count($validNameservers) > 13) {
            return json_encode(['code' => 1, 'message' => 'Error: At least 2 and at most 13 valid nameservers are required.']);
        }

        foreach ($validNameservers as $i => $nameserver) {
            if (strpos($nameserver, $zone) !== false) {
                $template .= '@        IN      NS      ns'.($i + 1).'.'.$zone.'.'.PHP_EOL;
                $template .= 'ns'.($i + 1).'     IN      A       '.$nameserver.PHP_EOL;
            } else {
                $template .= '@        IN      NS       '.$nameserver.'.'.PHP_EOL;
            }
        }
        if (file_exists($zoneFile)) {
            return json_encode(['code' => 1, 'message' => "Error: Zone $zone already exists."]);
        }

        try {
            if (preg_match('/[^a-zA-Z0-9\.\-_]/', basename($zoneFile))) {
                throw new \Exception('Invalid file name.');
            }
            $template = strip_tags($template);
            file_put_contents($zoneFile, $template);
        } catch (\Exception $e) {
            return json_encode(['code' => 1, 'message' => $e->getMessage()]);
        }

        $zoneConfig = 'zone "'.$zone.'" {
            type master;
            file "'.$zoneFile.'";
        };
        ';
        exec('rndc addzone '.$zone.' '.escapeshellarg($zoneConfig), $output, $return_var);
        if ($return_var != 0) {
            return json_encode(['code' => 1, 'message' => "Error: Failed to add zone $zone. Error: ".implode("\n", $output)]);
        }
        exec('rndc reload');

        return json_encode(['code' => 0, 'message' => "Zone $zone created successfully."]);
    }

    public function deleteZone(string $zone): string
    {
        if (! $zone) {
            throw new \Exception('Missing required parameter: zone');
        }
        $zone = htmlspecialchars($zone, ENT_QUOTES, 'utf-8');

        $zoneFile = '/root/zones/'.$zone;

        if (file_exists($zoneFile)) {
            unlink($zoneFile);
        }

        exec('rndc delzone '.escapeshellarg($zone), $output, $return_var);
        if ($return_var != 0) {
            return json_encode(['code' => 1, 'message' => "Error: Failed to delete zone $zone. Error: ".implode("\n", $output)]);
        }

        exec('rndc reload');

        return json_encode(['code' => 0, 'message' => "Zone $zone deleted successfully."]);
    }

    public function addRecord(
        string $zone,
        string $record,
        string $type,
        string $value,
        int $ttl = 3600
    ): string {
        if (! $zone || ! $record || ! $type || ! $value) {
            throw new \Exception('Missing required parameter: zone, record, type or value');
        }
        $zone = htmlspecialchars($zone, ENT_QUOTES, 'utf-8');
        $record = htmlspecialchars($record, ENT_QUOTES, 'utf-8');
        $type = htmlspecialchars($type, ENT_QUOTES, 'utf-8');
        $value = htmlspecialchars($value, ENT_QUOTES, 'utf-8');

        $zoneFile = '/root/zones/'.$zone;

        if (! file_exists($zoneFile)) {
            throw new \Exception('Zone file not found.');
        }

        $zoneContents = file_get_contents($zoneFile);
        if (strpos($zoneContents, "$record\tIN\t$type\t$value") !== false) {
            throw new \Exception('Record already exists.');
        }

        $newRecord = "$record\t$ttl\tIN\t$type\t$value\n";
        $zoneContents = rtrim($zoneContents).PHP_EOL.$newRecord;
        $zoneContents = $this->bumpSerial($zoneContents);
        file_put_contents($zoneFile, $zoneContents);

        exec('rndc reload');

        return json_encode(['code' => 0, 'message' => "Record $record for $zone created successfully."]);
    }

    public function deleteRecord(
        string $zone,
        string $record,
        string $type,
        string $value
    ): string {
        if (! $zone || ! $record || ! $type || ! $value) {
            throw new \Exception('Missing required parameter: zone, record, type or value');
        }
        $zone = htmlspecialchars($zone, ENT_QUOTES, 'utf-8');
        $record = htmlspecialchars($record, ENT_QUOTES, 'utf-8');
        $type = htmlspecialchars($type, ENT_QUOTES, 'utf-8');
        $value = htmlspecialchars($value, ENT_QUOTES, 'utf-8');

        $zoneFile = '/root/zones/'.$zone;

        if (! file_exists($zoneFile)) {
            throw new \Exception('Zone file not found.');
        }

        $zoneContents = file_get_contents($zoneFile);

        // Match record lines regardless of optional TTL field
        $pattern = '/^'.preg_quote($record, '/').'\s+\S*\s*IN\s+'.preg_quote($type, '/').'\s+'.preg_quote($value, '/').'\s*$/m';
        if (! preg_match($pattern, $zoneContents)) {
            throw new \Exception('Record not found.');
        }

        $zoneContents = preg_replace($pattern, '', $zoneContents);
        $zoneContents = $this->bumpSerial((string) $zoneContents);
        file_put_contents($zoneFile, $zoneContents);

        exec('rndc reload');

        return json_encode(['code' => 0, 'message' => "Record $record for $zone deleted successfully."]);
    }

    /**
     * Bump the SOA serial number in zone file contents.
     * Uses YYYYMMDDNN format: if today's date matches, increment NN; otherwise reset to YYYYMMDD01.
     */
    private function bumpSerial(string $zoneContents): string
    {
        $today = date('Ymd');

        return preg_replace_callback(
            '/(\d{10})\s*(;\s*serial)/i',
            function (array $matches) use ($today): string {
                $current = $matches[1];
                $currentDate = substr($current, 0, 8);
                $currentSeq = (int) substr($current, 8, 2);

                if ($currentDate === $today) {
                    $newSerial = $today.str_pad((string) ($currentSeq + 1), 2, '0', STR_PAD_LEFT);
                } else {
                    $newSerial = $today.'01';
                }

                return $newSerial.' '.$matches[2];
            },
            $zoneContents
        );
    }
}
