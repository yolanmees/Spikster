<?php

namespace App\Services;

use App\Models\Server;

class DnsService
{

    public function __construct(
        private string $namedConf = '/etc/bind/named.conf.local'
    ) { 
    }

    public function addZone(
        string $zone, 
        string $email, 
        array $nameservers
    ): string {
        if (!$zone || !$email|| !$nameservers) {
            throw new Exception('Missing required parameter: zone, email or nameservers');
        }
        $zone = htmlspecialchars($zone, ENT_QUOTES, 'utf-8');
        $email = htmlspecialchars($email, ENT_QUOTES, 'utf-8');
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = str_replace("@", ".", $email);
        } else {
            throw new Exception('Invalid email address');
        }
        foreach ($nameservers as $key => $value) {
            $nameservers[$key] = htmlspecialchars($value, ENT_QUOTES, 'utf-8');
        }

        $zoneFile = '/root/zones/' . $zone;
        $template = '$TTL    86400
        @       IN      SOA     ' . $nameservers[0] . '. ' . $email . ' (
                                ' . time() . ' ; serial
                                3600       ; refresh
                                1800       ; retry
                                604800     ; expire
                                86400 )    ; minimum
        ';
        $validNameservers = array();
        foreach ($nameservers as $i => $nameserver) {
            if (filter_var($nameserver, FILTER_VALIDATE_DOMAIN)) {
                $validNameservers[] = $nameserver;
            }
        }

        if (count($validNameservers) < 2 || count($validNameservers) > 13) {
            return json_encode(['code'=>1, 'message' => "Error: At least 2 and at most 13 valid nameservers are required."]);
        }

        foreach ($validNameservers as $i => $nameserver) {
            if (strpos($nameserver, $zone) !== false) {
                $template .= '@        IN      NS      ns' . ($i+1) . '.' . $zone . '.' . PHP_EOL;
                $template .= 'ns' . ($i+1) . '     IN      A       ' . $nameserver . PHP_EOL;
            } else {
                $template .= '@        IN      NS       ' . $nameserver . '.' . PHP_EOL;
            }
        }
        if (file_exists($zoneFile)) {
            return json_encode(['code'=>1, 'message' => "Error: Zone $zone already exists."]);
        }

        try {
            // Check the file name
            if (preg_match('/[^a-zA-Z0-9\.\-_]/', basename($zoneFile))) {
                throw new Exception("Invalid file name.");
            }
            // Check the template data
            $template = strip_tags($template);
            file_put_contents($zoneFile, $template);
        } catch (Exception $e) {
            return json_encode(['code'=>1, 'message' => $e->getMessage()]);
        }

        $$zoneConfig = 'zone "' . $zone . '" {
            type master;
            file "' . $zoneFile . '";
        };
        ';
        exec('rndc addzone ' . $zone . ' ' . escapeshellarg($$zoneConfig), $output, $return_var);
        if ($return_var != 0) {
            return json_encode(['code'=>1, 'message' => "Error: Failed to add zone $zone. Error: " . implode("\n", $output)]);
        }
        exec('rndc reload');
        return json_encode(['code'=>0 ,'message' => "Zone $zone created successfully."]);
    }

    public function deleterZone(string $zone): string 
    {
        if (!$zone) {
            throw new Exception('Missing required parameter: zone');
        }
        $zone = htmlspecialchars($zone, ENT_QUOTES, 'utf-8');

        $zoneFile = '/root/zones/' . $zone;
        // remove the zone file
        unlink($zoneFile);
        // remove the zone from named.conf
        $$namedContents = file_get_contents($this->namedConf);
        $start = strpos($$namedContents, "zone \"$zone\"");
        $end = strpos($$namedContents, "};", $start) + 2;
        $$zoneConfig = substr($$namedContents, $start, $end - $start);
        $$namedContents = str_replace($$zoneConfig, '', $$namedContents);
        file_put_contents($this->namedConf, $$namedContents);
        // reload bind
        exec('rndc reload');
        return json_encode(['code'=>0 ,'message' => "Zone $zone deleted successfully."]);
    }

    public function addRecord(
        string $zone, 
        string $record, 
        string $type, 
        string $value,
        int $ttl = 3600
    ): string
    {
        if (!$zone || !$record || !$type|| !$value) {
            throw new Exception('Missing required parameter: zone, record, type or value');
        }
        $zone = htmlspecialchars($zone, ENT_QUOTES, 'utf-8');
        $record = htmlspecialchars($record, ENT_QUOTES, 'utf-8');
        $type = htmlspecialchars($type, ENT_QUOTES, 'utf-8');
        $value = htmlspecialchars($value, ENT_QUOTES, 'utf-8');

        $zoneFile = '/root/zones/' . $zone;

        // Check if the zone file exists
        if (!file_exists($zoneFile)) {
            return "Error: Zone file not found.";
        }

        // Check if the record already exists in the zone file
        $zoneContents = file_get_contents($zoneFile);
        if (strpos($zoneContents, "$record\tIN\t$type\t$value") !== false) {
            return "Error: Record already exists.";
        }

        // Construct the new DNS record
        $$newRecord = "$record\tIN\t$type\t$value\n";

        // Append the new record to the zone file, add new line
        $zoneContents = rtrim($zoneContents) . PHP_EOL . $$newRecord;

        // Write the updated contents back to the zone file
        file_put_contents($zoneFile, $zoneContents);

        // Reload the BIND service to apply the changes
        exec('rndc reload');
        return json_encode(['code'=>0 ,'message' => "Record $record for $zone created successfully."]);
    }

    public function deleteRecord(
        string $zone, 
        string $record, 
        string $type, 
        string $value
    ): string
    {
        $result = $request->post();
        if (!$zone || !$record || !$type|| !$value) {
            throw new Exception('Missing required parameter: zone, record, type or value');
        }
        $zone = htmlspecialchars($zone, ENT_QUOTES, 'utf-8');
        $record = htmlspecialchars($record, ENT_QUOTES, 'utf-8');
        $type = htmlspecialchars($type, ENT_QUOTES, 'utf-8');
        $value = htmlspecialchars($value, ENT_QUOTES, 'utf-8');

        $zoneFile = '/root/zones/' . $zone;

        // Check if the zone file exists
        if (!file_exists($zoneFile)) {
            return "Error: Zone file not found.";
        }

        // Read the current contents of the zone file
        $zoneContents = file_get_contents($zoneFile);

        // Construct the DNS record to be removed
        $recordToRemove = "$record\tIN\t$type\t$value\n";

        // Remove the record from the zone file
        $zoneContents = str_replace($recordToRemove, "", $zoneContents);
        // check if the record is not existing in the zone file
        if ($zoneContents === file_get_contents($zoneFile)) {
            return "Error: Record not found.";
        }
        // Write the updated contents back to the zone file
        file_put_contents($zoneFile, $zoneContents);

        // Reload the BIND service to apply the changes
        exec('rndc reload');
        return json_encode(['code'=>0 ,'message' => "Record $record for $zone created successfully."]);
    }

}
