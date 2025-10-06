<?php

namespace App\Jobs\Backup;

use App\Models\Backup;
use App\Models\BackupStorageLocation;
use App\Services\SSHService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UploadToS3SSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour for large uploads
    public $tries = 2;

    protected Backup $backup;
    protected BackupStorageLocation $storageLocation;

    /**
     * Create a new job instance.
     */
    public function __construct(Backup $backup)
    {
        $this->backup = $backup;

        // Find S3 storage location
        $this->storageLocation = BackupStorageLocation::where('name', $backup->storage_location)
            ->where('type', 's3')
            ->where('is_active', true)
            ->firstOrFail();
    }

    /**
     * Execute the job.
     */
    public function handle(SSHService $ssh): void
    {
        try {
            Log::info("Starting S3 upload for backup", [
                'backup_id' => $this->backup->id,
                'storage_location' => $this->storageLocation->name,
            ]);

            $server = $this->backup->site->server;
            $ssh->connect($server);

            // Get S3 credentials from storage location config
            $credentials = $this->storageLocation->getCredentials();

            $bucket = $credentials['bucket'];
            $region = $credentials['region'] ?? 'us-east-1';
            $accessKey = $credentials['access_key'];
            $secretKey = $credentials['secret_key'];
            $endpoint = $credentials['endpoint'] ?? null;
            $path = $credentials['path'] ?? 'backups';

            // Check if AWS CLI is installed
            $awsInstalled = $ssh->execute("which aws > /dev/null 2>&1 && echo 'yes' || echo 'no'");

            if (trim($awsInstalled) !== 'yes') {
                Log::info("Installing AWS CLI");
                $this->installAwsCli($ssh);
            }

            // Configure AWS credentials
            $ssh->execute("mkdir -p ~/.aws");

            $configContent = "[default]\n" .
                           "aws_access_key_id = {$accessKey}\n" .
                           "aws_secret_access_key = {$secretKey}\n" .
                           "region = {$region}";

            $ssh->execute("echo '{$configContent}' > ~/.aws/credentials");

            // Build S3 path
            $s3Key = "{$path}/{$this->backup->site->domain}/{$this->backup->filename}";
            $s3Uri = "s3://{$bucket}/{$s3Key}";

            // Build upload command
            $uploadCmd = "aws s3 cp {$this->backup->filepath} {$s3Uri}";

            // Add endpoint if using S3-compatible storage (like DigitalOcean Spaces)
            if ($endpoint) {
                $uploadCmd .= " --endpoint-url {$endpoint}";
            }

            // Add storage class if specified
            if (!empty($credentials['storage_class'])) {
                $uploadCmd .= " --storage-class {$credentials['storage_class']}";
            }

            // Execute upload
            Log::info("Uploading backup to S3: {$s3Uri}");
            $output = $ssh->execute($uploadCmd);

            // Verify upload was successful
            $verifyCmd = "aws s3 ls {$s3Uri}";
            if ($endpoint) {
                $verifyCmd .= " --endpoint-url {$endpoint}";
            }

            $verifyOutput = $ssh->execute($verifyCmd);
            if (empty(trim($verifyOutput))) {
                throw new \Exception("S3 upload verification failed");
            }

            // Update backup metadata
            $metadata = $this->backup->metadata ?? [];
            $metadata['s3_upload'] = [
                'bucket' => $bucket,
                'key' => $s3Key,
                'region' => $region,
                'uploaded_at' => now()->toIso8601String(),
            ];
            $this->backup->metadata = $metadata;
            $this->backup->save();

            // Optionally delete local backup after successful upload
            if (config('backup.delete_local_after_remote_upload', false)) {
                Log::info("Deleting local backup after successful S3 upload");
                $ssh->execute("rm -f {$this->backup->filepath}");
            }

            Log::info("Backup uploaded to S3 successfully", [
                'backup_id' => $this->backup->id,
                's3_uri' => $s3Uri,
            ]);

        } catch (\Exception $e) {
            Log::error("S3 upload failed", [
                'backup_id' => $this->backup->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Install AWS CLI on the server
     */
    protected function installAwsCli(SSHService $ssh): void
    {
        // Install AWS CLI v2
        $commands = [
            "cd /tmp",
            "curl 'https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip' -o 'awscliv2.zip'",
            "apt-get install -y unzip",
            "unzip -q awscliv2.zip",
            "sudo ./aws/install",
            "rm -rf aws awscliv2.zip",
        ];

        foreach ($commands as $cmd) {
            $ssh->execute($cmd);
        }

        Log::info("AWS CLI installed successfully");
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("UploadToS3SSH job failed", [
            'backup_id' => $this->backup->id,
            'storage_location' => $this->storageLocation->name,
            'error' => $exception->getMessage(),
        ]);
    }
}
