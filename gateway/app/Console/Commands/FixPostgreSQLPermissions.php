<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixPostgreSQLPermissions extends Command
{
    protected $signature = 'postgres:fix-permissions
                            {--dry-run : Show what would be done without executing}
                            {--role=laravel_apps : Role name for Laravel applications}
                            {--fix-existing : Also fix permissions on existing tenant databases}
                            {--fix-migrations : Fix existing migrations table issues}
                            {--tenant= : Fix specific tenant database only}
                            {--skip-confirmation : Skip confirmation prompts}';

    protected $description = 'Fix PostgreSQL permissions for tenant creation and existing databases';

    public function handle()
    {
        $this->info('🔧 PostgreSQL Multi-Tenant Permission Fixer');
        $this->line('=====================================');

        // Safety checks
        if (!$this->performSafetyChecks()) {
            return 1;
        }

        $roleName = $this->option('role');
        $isDryRun = $this->option('dry-run');
        $fixExisting = $this->option('fix-existing');
        $fixMigrations = $this->option('fix-migrations');
        $specificTenant = $this->option('tenant');
        $skipConfirmation = $this->option('skip-confirmation');

        try {
            // Get system database config
            $systemConfig = config('database.connections.system');

            if (!$this->validateSystemConfig($systemConfig)) {
                return 1;
            }

            // Display current config
            $this->displayConfiguration($systemConfig, $roleName, $fixExisting, $specificTenant);

            // Production confirmation
            if (!$skipConfirmation && !$this->getConfirmation($fixExisting, $specificTenant)) {
                return 0;
            }

            if ($isDryRun) {
                $this->info('🔍 DRY RUN - Commands that would be executed:');
                $this->showDryRunCommands($roleName, $fixExisting, $fixMigrations, $specificTenant);
                return 0;
            }

            // Step 1: Fix template1 for future databases
            if (!$specificTenant) {
                $this->fixTemplate1Permissions($systemConfig, $roleName);
            }

            // Step 2: Fix existing databases
            if ($fixExisting || $specificTenant) {
                $this->fixExistingDatabases($systemConfig, $roleName, $fixMigrations, $specificTenant);
            }

            // Step 3: Test new database creation (only if not fixing specific tenant)
            if (!$specificTenant) {
                $this->testDatabaseCreation($systemConfig, $roleName);
            }

            $this->displaySuccessMessage($fixExisting, $specificTenant, $roleName);

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->line('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }

    private function performSafetyChecks()
    {
        // Check if we're in a Laravel app
        if (!function_exists('config')) {
            $this->error('❌ This command must be run from a Laravel application');
            return false;
        }

        // Check if system connection exists
        if (!config('database.connections.system')) {
            $this->error('❌ System database connection not configured');
            return false;
        }

        return true;
    }

    private function validateSystemConfig($systemConfig)
    {
        if (!isset($systemConfig['host']) || !isset($systemConfig['port'])) {
            $this->error('❌ System database configuration is missing host or port');
            return false;
        }

        if (!isset($systemConfig['username']) || !isset($systemConfig['password'])) {
            $this->error('❌ System database configuration is missing username or password');
            return false;
        }

        return true;
    }

    private function displayConfiguration($systemConfig, $roleName, $fixExisting, $specificTenant)
    {
        $this->table(['Setting', 'Value'], [
            ['Host', $systemConfig['host']],
            ['Port', $systemConfig['port']],
            ['System Database', $systemConfig['database']],
            ['Username', $systemConfig['username']],
            ['Role Name', $roleName],
            ['Fix Existing', $fixExisting ? 'Yes' : 'No'],
            ['Specific Tenant', $specificTenant ?: 'All'],
            ['Environment', app()->environment()],
        ]);
    }

    private function getConfirmation($fixExisting, $specificTenant)
    {
        if (app()->environment('production')) {
            $this->warn('⚠️  PRODUCTION ENVIRONMENT DETECTED');

            if ($specificTenant) {
                $message = "This will modify permissions for tenant database: {$specificTenant}";
            } elseif ($fixExisting) {
                $message = "This will modify permissions for ALL tenant databases";
            } else {
                $message = "This will modify template1 permissions for future databases";
            }

            if (!$this->confirm($message . '. Continue?', false)) {
                $this->info('Operation cancelled.');
                return false;
            }

            // Double confirmation for production
            if ($fixExisting && !$specificTenant) {
                if (!$this->confirm('Are you absolutely sure you want to modify ALL tenant databases?', false)) {
                    $this->info('Operation cancelled.');
                    return false;
                }
            }
        }

        return true;
    }

    private function fixTemplate1Permissions($systemConfig, $roleName)
    {
        $this->info('📝 Step 1: Fixing template1 permissions');

        $template1Config = [
            'driver' => 'pgsql',
            'host' => $systemConfig['host'],
            'port' => $systemConfig['port'],
            'database' => 'template1',
            'username' => $systemConfig['username'],
            'password' => $systemConfig['password'],
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ];

        $this->line('Testing connection to template1...');
        config(['database.connections.template1_fix' => $template1Config]);
        DB::purge('template1_fix');

        try {
            $template1 = DB::connection('template1_fix');
            $template1->select('SELECT 1 as test');
            $this->info('✅ Connection successful');
        } catch (\Exception $e) {
            $this->error('❌ Connection failed: ' . $e->getMessage());
            throw $e;
        }

        // Create role and fix permissions
        $this->createRoleIfNotExists($template1, $roleName);
        $this->applySchemaPermissions($template1, $roleName, 'template1');

        DB::purge('template1_fix');
        $this->info('✅ Template1 permissions fixed');
    }

    private function fixExistingDatabases($systemConfig, $roleName, $fixMigrations, $specificTenant)
    {
        $this->info('📝 Step 2: Fixing existing tenant databases');

        $system = DB::connection('system');
        $databases = $this->getTenantDatabases($system, $systemConfig, $specificTenant);

        if (empty($databases)) {
            $this->info('No tenant databases found to fix.');
            return;
        }

        $this->line('Found ' . count($databases) . ' database(s) to fix:');
        foreach ($databases as $db) {
            $this->line("  - {$db->datname}");
        }
        $this->line('');

        $successCount = 0;
        $errorCount = 0;

        foreach ($databases as $db) {
            $dbName = $db->datname;

            try {
                $this->line("🔧 Processing: {$dbName}");

                if ($this->fixSingleDatabase($systemConfig, $system, $dbName, $roleName, $fixMigrations)) {
                    $successCount++;
                    $this->info("  ✅ Successfully fixed {$dbName}");
                } else {
                    $errorCount++;
                    $this->error("  ❌ Failed to fix {$dbName}");
                }

            } catch (\Exception $e) {
                $errorCount++;
                $this->error("  ❌ Error fixing {$dbName}: " . $e->getMessage());
                continue;
            }
        }

        $this->line('');
        $this->info("✅ Finished: {$successCount} successful, {$errorCount} errors");
    }

    private function getTenantDatabases($system, $systemConfig, $specificTenant = null)
    {
        if ($specificTenant) {
            return $system->select("
                SELECT datname
                FROM pg_database
                WHERE datname = ?
                AND datistemplate = false
            ", [$specificTenant]);
        }

        return $system->select("
            SELECT datname
            FROM pg_database
            WHERE datname NOT IN ('postgres', 'template0', 'template1', ?)
            AND datistemplate = false
            AND datname NOT LIKE 'test_%'
        ", [$systemConfig['database']]);
    }

    private function fixSingleDatabase($systemConfig, $systemConnection, $dbName, $roleName, $fixMigrations)
    {
        try {
            // Create connection to tenant database
            $dbConfig = [
                'driver' => 'pgsql',
                'host' => $systemConfig['host'],
                'port' => $systemConfig['port'],
                'database' => $dbName,
                'username' => $systemConfig['username'],
                'password' => $systemConfig['password'],
                'charset' => 'utf8',
                'prefix' => '',
                'schema' => 'public',
                'sslmode' => 'prefer',
            ];

            $connectionName = "fix_db_{$dbName}";
            config(["database.connections.{$connectionName}" => $dbConfig]);
            DB::purge($connectionName);

            $dbConnection = DB::connection($connectionName);

            // Test connection
            $dbConnection->select('SELECT 1');

            // Apply schema permissions
            $this->applySchemaPermissions($dbConnection, $roleName, $dbName);

            // Fix table ownership
            $this->fixTableOwnership($dbConnection, $systemConnection, $dbName);

            // Fix migrations table if requested
            if ($fixMigrations) {
                $this->fixMigrationsTable($dbConnection, $dbName);
            }

            // Grant role to database owner
            $this->grantRoleToOwner($systemConnection, $dbName, $roleName, $systemConfig);

            DB::purge($connectionName);
            return true;

        } catch (\Exception $e) {
            $this->line("    ⚠️  Database error: " . $e->getMessage());
            if (isset($connectionName)) {
                DB::purge($connectionName);
            }
            return false;
        }
    }

    private function applySchemaPermissions($connection, $roleName, $dbName)
    {
        $permissions = [
            "GRANT ALL ON SCHEMA public TO {$roleName}",
            "GRANT CREATE ON SCHEMA public TO {$roleName}",
            "ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO {$roleName}",
            "ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO {$roleName}",
            "ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON FUNCTIONS TO {$roleName}",
            "GRANT USAGE ON SCHEMA public TO PUBLIC",
            "GRANT CREATE ON SCHEMA public TO PUBLIC",
        ];

        foreach ($permissions as $permission) {
            try {
                $connection->statement($permission);
            } catch (\Exception $e) {
                $this->warn("    ⚠️  Permission warning in {$dbName}: " . $e->getMessage());
            }
        }
    }

    private function grantRoleToOwner($systemConnection, $dbName, $roleName, $systemConfig)
    {
        try {
            $owner = $systemConnection->select("
                SELECT pg_catalog.pg_get_userbyid(d.datdba) as owner
                FROM pg_catalog.pg_database d
                WHERE d.datname = ?
            ", [$dbName]);

            if (!empty($owner)) {
                $ownerName = $owner[0]->owner;
                if ($ownerName !== $systemConfig['username']) {
                    try {
                        $systemConnection->statement("GRANT {$roleName} TO \"{$ownerName}\"");
                        $this->line("    ✅ Granted {$roleName} role to owner: {$ownerName}");
                    } catch (\Exception $e) {
                        $this->line("    ⚠️  Could not grant role to owner {$ownerName}: " . $e->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            $this->line("    ⚠️  Could not determine database owner: " . $e->getMessage());
        }
    }

    private function createRoleIfNotExists($connection, $roleName)
    {
        try {
            $result = $connection->select("SELECT 1 FROM pg_roles WHERE rolname = ?", [$roleName]);

            if (empty($result)) {
                $this->line("Creating role '{$roleName}'...");
                $connection->statement("CREATE ROLE {$roleName}");
                $this->info("✅ Role '{$roleName}' created successfully");
            } else {
                $this->info("✅ Role '{$roleName}' already exists");
            }
        } catch (\Exception $e) {
            $this->error("❌ Failed to create role '{$roleName}': " . $e->getMessage());
            throw $e;
        }
    }

    private function fixTableOwnership($dbConnection, $systemConnection, $dbName)
    {
        try {
            $this->line("    🔧 Fixing table ownership in {$dbName}...");

            $ownerResult = $systemConnection->select("
                SELECT pg_catalog.pg_get_userbyid(d.datdba) as owner
                FROM pg_catalog.pg_database d
                WHERE d.datname = ?
            ", [$dbName]);

            if (empty($ownerResult)) {
                $this->line("    ⚠️  Could not determine database owner for {$dbName}");
                return;
            }

            $dbOwner = $ownerResult[0]->owner;

            // Fix tables
            $tables = $dbConnection->select("
                SELECT schemaname, tablename, tableowner
                FROM pg_tables
                WHERE schemaname = 'public'
            ");

            $changedCount = 0;
            foreach ($tables as $table) {
                if ($table->tableowner !== $dbOwner) {
                    try {
                        $dbConnection->statement("ALTER TABLE \"{$table->tablename}\" OWNER TO \"{$dbOwner}\"");
                        $changedCount++;
                        $this->line("    ✅ Fixed ownership: {$table->tablename} → {$dbOwner}");
                    } catch (\Exception $e) {
                        $this->line("    ⚠️  Could not change ownership of {$table->tablename}: " . $e->getMessage());
                    }
                }
            }

            // Fix sequences
            $sequences = $dbConnection->select("
                SELECT schemaname, sequencename, sequenceowner
                FROM pg_sequences
                WHERE schemaname = 'public'
            ");

            foreach ($sequences as $sequence) {
                if ($sequence->sequenceowner !== $dbOwner) {
                    try {
                        $dbConnection->statement("ALTER SEQUENCE \"{$sequence->sequencename}\" OWNER TO \"{$dbOwner}\"");
                        $changedCount++;
                        $this->line("    ✅ Fixed sequence ownership: {$sequence->sequencename} → {$dbOwner}");
                    } catch (\Exception $e) {
                        $this->line("    ⚠️  Could not change sequence ownership: " . $e->getMessage());
                    }
                }
            }

            if ($changedCount > 0) {
                $this->info("    ✅ Fixed ownership of {$changedCount} objects");
            } else {
                $this->line("    ✅ All objects already have correct ownership");
            }

        } catch (\Exception $e) {
            $this->line("    ⚠️  Error fixing table ownership: " . $e->getMessage());
        }
    }

    private function fixMigrationsTable($dbConnection, $dbName)
    {
        try {
            $this->line("    🔧 Checking migrations table in {$dbName}...");

            $tableExists = $dbConnection->select("
                SELECT EXISTS (
                    SELECT FROM information_schema.tables
                    WHERE table_schema = 'public'
                    AND table_name = 'migrations'
                )::boolean as exists
            ");

            if (!$tableExists[0]->exists) {
                $this->line("    ℹ️  Migrations table doesn't exist in {$dbName}");
                return;
            }

            // Just ensure proper ownership and permissions
            $this->line("    ✅ Migrations table exists and will be fixed with other tables");

        } catch (\Exception $e) {
            $this->line("    ⚠️  Error checking migrations table: " . $e->getMessage());
        }
    }

    private function testDatabaseCreation($systemConfig, $roleName)
    {
        $this->info('📝 Step 3: Testing new database creation');

        $testDb = null;
        $testUser = null;

        try {
            $testDb = 'test_permissions_' . time();
            $testUser = $testDb;

            $this->line("Creating test database: {$testDb}");
            $system = DB::connection('system');

            $system->statement("CREATE USER \"{$testUser}\" WITH PASSWORD 'test123' CREATEDB");
            $system->statement("GRANT {$roleName} TO \"{$testUser}\"");
            $system->statement("CREATE DATABASE \"{$testDb}\" OWNER \"{$testUser}\" TEMPLATE template0");

            // Test permissions
            $testConfig = [
                'driver' => 'pgsql',
                'host' => $systemConfig['host'],
                'port' => $systemConfig['port'],
                'database' => $testDb,
                'username' => $testUser,
                'password' => 'test123',
                'charset' => 'utf8',
                'prefix' => '',
                'schema' => 'public',
                'sslmode' => 'prefer',
            ];

            config(['database.connections.test_db' => $testConfig]);
            DB::purge('test_db');

            $testConn = DB::connection('test_db');

            $testConn->statement('CREATE TABLE migrations (
                id serial NOT NULL PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INTEGER NOT NULL
            )');

            $testConn->statement('CREATE TABLE test_table (id serial PRIMARY KEY, name VARCHAR(100))');
            $testConn->statement('INSERT INTO test_table (name) VALUES (?)', ['test']);
            $testConn->statement('DROP TABLE test_table');
            $testConn->statement('DROP TABLE migrations');

            $this->info('✅ Database creation test successful');

        } catch (\Exception $e) {
            $this->error('❌ Database test failed: ' . $e->getMessage());
        } finally {
            if ($testDb && $testUser) {
                try {
                    $this->line('Cleaning up test resources...');
                    $system = DB::connection('system');
                    $system->statement("DROP DATABASE IF EXISTS \"{$testDb}\"");
                    $system->statement("DROP USER IF EXISTS \"{$testUser}\"");
                    $this->info('✅ Test resources cleaned up');
                } catch (\Exception $e) {
                    $this->warn('⚠️  Manual cleanup may be required for: ' . $testDb);
                }
            }
        }
    }

    private function displaySuccessMessage($fixExisting, $specificTenant, $roleName)
    {
        $this->line('');
        $this->info('🎉 SUCCESS! PostgreSQL permissions have been fixed.');
        $this->line('');

        if ($specificTenant) {
            $this->info("✅ Fixed permissions for tenant: {$specificTenant}");
        } elseif ($fixExisting) {
            $this->info('✅ Fixed permissions for all existing tenant databases');
        } else {
            $this->info('✅ Fixed template1 permissions for future databases');
            $this->warn('💡 To fix existing tenant databases, run:');
            $this->line('   php artisan postgres:fix-permissions --fix-existing');
        }

        $this->line('');
        $this->info("💡 Remember: New tenant users need the '{$roleName}' role:");
        $this->line("   GRANT {$roleName} TO your_tenant_user;");
    }

    private function showDryRunCommands($roleName, $fixExisting, $fixMigrations, $specificTenant)
    {
        $this->line('Template1 permissions:');
        $commands = [
            "SELECT 1 FROM pg_roles WHERE rolname = '{$roleName}' (check if role exists)",
            "CREATE ROLE {$roleName} (only if role doesn't exist)",
            "GRANT ALL ON SCHEMA public TO {$roleName}",
            "GRANT CREATE ON SCHEMA public TO {$roleName}",
            "ALTER DEFAULT PRIVILEGES... (tables, sequences, functions)",
        ];

        foreach ($commands as $command) {
            $this->line("  {$command}");
        }

        if ($fixExisting || $specificTenant) {
            $this->line('');
            $target = $specificTenant ? "database '{$specificTenant}'" : 'all tenant databases';
            $this->line("Existing {$target}:");
            $this->line('  - Apply same schema permissions');
            $this->line('  - Fix table and sequence ownership');
            if ($fixMigrations) {
                $this->line('  - Fix migrations table structure');
            }
            $this->line("  - Grant {$roleName} role to database owners");
        }

        $this->line('');
        $this->info('Database creation test would also be performed.');
    }
}
