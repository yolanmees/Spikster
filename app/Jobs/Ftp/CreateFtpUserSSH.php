<?php

namespace App\Jobs\Ftp;

use App\Models\FtpUser;
use App\Services\SSHService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateFtpUserSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 3;

    public function __construct(
        public FtpUser $ftpUser
    ) {}

    public function handle(): void
    {
        $ssh = new SSHService($this->ftpUser->server);

        try {
            Log::info("Creating FTP user on server", [
                'username' => $this->ftpUser->username,
                'server_id' => $this->ftpUser->server_id,
            ]);

            // 1. Install vsftpd if not installed
            $this->ensureVsftpdInstalled($ssh);

            // 2. Create/update virtual user database
            $this->createVirtualUser($ssh);

            // 3. Create user-specific configuration
            $this->createUserConfig($ssh);

            // 4. Set up chroot directory
            $this->setupChrootDirectory($ssh);

            // 5. Reload vsftpd
            $ssh->execute('systemctl reload vsftpd || service vsftpd reload');

            Log::info("FTP user created successfully", [
                'username' => $this->ftpUser->username,
                'home_directory' => $this->ftpUser->home_directory,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to create FTP user", [
                'username' => $this->ftpUser->username,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    protected function ensureVsftpdInstalled(SSHService $ssh): void
    {
        $installed = $ssh->execute('dpkg -l | grep -w vsftpd | wc -l');

        if (trim($installed) === '0') {
            Log::info("Installing vsftpd...");

            $ssh->execute('apt-get update');
            $ssh->execute('DEBIAN_FRONTEND=noninteractive apt-get install -y vsftpd db-util libpam-pwdfile');

            // Configure main vsftpd.conf
            $this->configureVsftpd($ssh);

            // Start vsftpd
            $ssh->execute('systemctl enable vsftpd');
            $ssh->execute('systemctl start vsftpd');
        }
    }

    protected function configureVsftpd(SSHService $ssh): void
    {
        $config = <<<'EOF'
# Spikster vsftpd configuration
listen=YES
listen_ipv6=NO

# Access Control
anonymous_enable=NO
local_enable=YES
write_enable=YES
local_umask=022

# Security - Chroot
chroot_local_user=YES
allow_writeable_chroot=YES
secure_chroot_dir=/var/run/vsftpd/empty

# Virtual Users
guest_enable=YES
guest_username=www-data
virtual_use_local_privs=YES
user_config_dir=/etc/vsftpd/users
pam_service_name=vsftpd-virtual

# Logging
xferlog_enable=YES
xferlog_file=/var/log/vsftpd/vsftpd.log
log_ftp_protocol=YES
dirmessage_enable=YES
use_localtime=YES

# SSL/TLS Configuration
ssl_enable=YES
allow_anon_ssl=NO
force_local_data_ssl=YES
force_local_logins_ssl=YES
ssl_tlsv1=YES
ssl_sslv2=NO
ssl_sslv3=NO
require_ssl_reuse=NO
ssl_ciphers=HIGH
rsa_cert_file=/etc/ssl/certs/ssl-cert-snakeoil.pem
rsa_private_key_file=/etc/ssl/private/ssl-cert-snakeoil.key

# Passive Mode
pasv_enable=YES
pasv_min_port=40000
pasv_max_port=50000
pasv_address=
port_enable=YES
connect_from_port_20=YES

# Performance & Limits
max_clients=100
max_per_ip=5
idle_session_timeout=600
data_connection_timeout=120

# File Operations
file_open_mode=0666
local_root=

# Misc
text_userdb_names=NO
ls_recurse_enable=NO
EOF;

        $ssh->execute("cat > /etc/vsftpd.conf << 'VSFTPD_EOF'\n{$config}\nVSFTPD_EOF");
        $ssh->execute('mkdir -p /etc/vsftpd/users');
        $ssh->execute('mkdir -p /var/log/vsftpd');
        $ssh->execute('mkdir -p /var/run/vsftpd/empty');
        $ssh->execute('touch /var/log/vsftpd/vsftpd.log');
        $ssh->execute('touch /etc/vsftpd/vusers.txt');

        // Configure PAM for virtual users
        $pamConfig = <<<'EOF'
auth required pam_userdb.so db=/etc/vsftpd/vusers
account required pam_userdb.so db=/etc/vsftpd/vusers
EOF;
        $ssh->execute("cat > /etc/pam.d/vsftpd-virtual << 'PAM_EOF'\n{$pamConfig}\nPAM_EOF");

        Log::info("vsftpd configured successfully");
    }

    protected function createVirtualUser(SSHService $ssh): void
    {
        $username = preg_replace('/[^a-zA-Z0-9._-]/', '', $this->ftpUser->username);
        if (empty($username)) {
            throw new \RuntimeException('Invalid FTP username.');
        }

        // Get the plain text password (before it was hashed in the model)
        // We need to store it in plain text for vsftpd's Berkeley DB
        $password = $this->generateRandomPassword();

        // Check if user already exists in vusers.txt
        $existingUser = $ssh->execute("grep -c '^{$username}$' /etc/vsftpd/vusers.txt || true");

        if (trim($existingUser) === '0') {
            // Add new user
            $ssh->execute("echo '{$username}' >> /etc/vsftpd/vusers.txt");
            $ssh->execute("echo '{$password}' >> /etc/vsftpd/vusers.txt");

            Log::info("Added FTP user to vusers.txt", ['username' => $username]);
        } else {
            // Update existing user password
            // Remove old entry and add new one
            $ssh->execute("sed -i '/^" . addcslashes($username, "/") . "$/,+1d' /etc/vsftpd/vusers.txt");
            $ssh->execute("echo '{$username}' >> /etc/vsftpd/vusers.txt");
            $ssh->execute("echo '{$password}' >> /etc/vsftpd/vusers.txt");

            Log::info("Updated FTP user password in vusers.txt", ['username' => $username]);
        }

        // Generate Berkeley DB from vusers.txt
        $ssh->execute('db_load -T -t hash -f /etc/vsftpd/vusers.txt /etc/vsftpd/vusers.db');
        $ssh->execute('chmod 600 /etc/vsftpd/vusers.db');
        $ssh->execute('chmod 600 /etc/vsftpd/vusers.txt');

        Log::info("Berkeley DB updated", ['username' => $username]);
    }

    protected function createUserConfig(SSHService $ssh): void
    {
        $username = $this->ftpUser->username;
        $homeDir = $this->ftpUser->home_directory;
        $maxConn = $this->ftpUser->max_connections;

        $permissions = $this->ftpUser->permissions;
        $writeEnable = ($permissions['write'] ?? true) ? 'YES' : 'NO';
        $deleteEnable = ($permissions['delete'] ?? true) ? 'YES' : 'NO';

        $config = "local_root={$homeDir}\n";
        $config .= "write_enable={$writeEnable}\n";
        $config .= "anon_upload_enable={$writeEnable}\n";
        $config .= "anon_mkdir_write_enable={$writeEnable}\n";
        $config .= "anon_other_write_enable={$deleteEnable}\n";
        $config .= "max_clients={$maxConn}\n";
        $config .= "max_per_ip={$maxConn}\n";

        if ($this->ftpUser->bandwidth_limit_kbps) {
            $bytesPerSecond = $this->ftpUser->bandwidth_limit_kbps * 1024;
            $config .= "local_max_rate={$bytesPerSecond}\n";
        }

        if ($this->ftpUser->allowed_ip) {
            // Create tcp_wrappers rule
            $ssh->execute("echo 'vsftpd: {$this->ftpUser->allowed_ip}' >> /etc/hosts.allow");
        }

        // Write user config
        $escapedConfig = str_replace("'", "'\\''", $config);
        $ssh->execute("cat > /etc/vsftpd/users/{$username} << 'USER_EOF'\n{$config}\nUSER_EOF");

        Log::info("Created user config", [
            'username' => $username,
            'config_path' => "/etc/vsftpd/users/{$username}",
        ]);
    }

    protected function setupChrootDirectory(SSHService $ssh): void
    {
        $homeDir = $this->ftpUser->home_directory;

        // Create directory if not exists
        $ssh->execute("mkdir -p {$homeDir}");

        // Set ownership to www-data
        $ssh->execute("chown -R www-data:www-data {$homeDir}");

        // Set permissions (755 for chroot compatibility)
        $ssh->execute("chmod 755 {$homeDir}");

        // Create .ftpquota file for quota tracking
        $ssh->execute("touch {$homeDir}/.ftpquota");
        $ssh->execute("echo '0' > {$homeDir}/.ftpquota");
        $ssh->execute("chown www-data:www-data {$homeDir}/.ftpquota");

        // Create standard subdirectories if they don't exist
        $ssh->execute("mkdir -p {$homeDir}/public_html");
        $ssh->execute("mkdir -p {$homeDir}/logs");
        $ssh->execute("mkdir -p {$homeDir}/tmp");
        $ssh->execute("chown -R www-data:www-data {$homeDir}");

        Log::info("Chroot directory set up", [
            'home_directory' => $homeDir,
        ]);
    }

    protected function generateRandomPassword(int $length = 16): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $password;
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("CreateFtpUserSSH job failed", [
            'username' => $this->ftpUser->username,
            'error' => $exception->getMessage(),
        ]);
    }
}
