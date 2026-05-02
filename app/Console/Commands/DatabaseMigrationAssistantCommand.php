<?php

namespace App\Console\Commands;

use App\Models\Database;
use App\Services\DatabaseService;
use Illuminate\Console\Command;

class DatabaseMigrationAssistantCommand extends Command
{
    protected $signature = 'spikster:db-migrate
        {--source= : Source database connection from config/database.php}
        {--source-host= : Source database host}
        {--source-port=3306 : Source database port}
        {--source-user= : Source database user}
        {--source-pass= : Source database password}
        {--source-db= : Source database name}
        {--dry-run : Preview what will be migrated}';

    protected $description = 'Migrate external databases into Spikster-managed databases';

    public function handle(DatabaseService $databaseService): int
    {
        $sourceHost = $this->option('source-host');
        $sourcePort = $this->option('source-port');
        $sourceUser = $this->option('source-user');
        $sourcePass = $this->option('source-pass');
        $sourceDb = $this->option('source-db');
        $dryRun = $this->option('dry-run');

        if (! $sourceHost) {
            $this->error('Source database connection is required. Use --source-host.');
            return 1;
        }

        // Test connection
        $this->line("Testing connection to {$sourceHost}:{$sourcePort}...");

        $testOutput = [];
        $testCode = 0;
        exec("mysql --host={$sourceHost} --port={$sourcePort} --user={$sourceUser} --password={$sourcePass} -e 'SELECT 1' 2>&1", $testOutput, $testCode);

        if ($testCode !== 0) {
            $this->error("Connection failed: " . implode("\n", $testOutput));
            return 1;
        }

        $this->info('Connection successful.');

        // Get database list
        $dbsOutput = [];
        exec(
            "mysql --host={$sourceHost} --port={$sourcePort} --user={$sourceUser} --password={$sourcePass} -e 'SHOW DATABASES' 2>&1",
            $dbsOutput,
            $dbsCode
        );

        $databases = array_slice($dbsOutput, 1); // Skip header
        $databases = array_filter($databases, fn ($db) => ! in_array($db, ['information_schema', 'performance_schema', 'mysql', 'sys']));

        $this->info('Available databases:');
        foreach ($databases as $db) {
            $this->line("  - {$db}");
        }

        if ($dryRun) {
            $this->info('Dry run: No changes made.');
            $this->table(['Database', 'Action'], array_map(fn ($db) => [$db, 'Would be imported'], $databases));
            return 0;
        }

        $targetDb = $this->anticipate('Target database name in Spikster', [$sourceDb ?: ($databases[0] ?? '')]);

        foreach ($databases as $db) {
            if ($this->confirm("Import '{$db}' into '{$targetDb}'?")) {
                $this->line("Importing {$db}...");

                $dumpFile = storage_path("app/migrations/{$db}_".now()->format('Ymd_His').'.sql');
                $dumpDir = dirname($dumpFile);

                if (! is_dir($dumpDir)) {
                    mkdir($dumpDir, 0755, true);
                }

                // Dump source
                exec(
                    "mysqldump --host={$sourceHost} --port={$sourcePort} --user={$sourceUser} --password={$sourcePass} {$db} > {$dumpFile} 2>&1",
                    $dumpOutput,
                    $dumpCode
                );

                if ($dumpCode !== 0) {
                    $this->error("Dump failed for {$db}");
                    continue;
                }

                // Import to Spikster's MySQL
                $localDb = $targetDb ?: $db;
                exec("mysql -e 'CREATE DATABASE IF NOT EXISTS {$localDb}' 2>&1");
                exec("mysql {$localDb} < {$dumpFile} 2>&1", $importOutput, $importCode);

                if ($importCode === 0) {
                    $this->info("✓ {$db} imported successfully as {$localDb}");

                    Database::create([
                        'database_name' => $localDb,
                        'site_id' => null,
                        'user_id' => auth()->id(),
                    ]);
                } else {
                    $this->error("✗ Import failed for {$db}");
                }
            }
        }

        $this->info('Migration complete.');

        return 0;
    }
}
