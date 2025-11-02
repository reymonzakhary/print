<?php

namespace App\Console\Commands;

use App\Models\Website;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Hyn\Tenancy\Environment;
use Exception;

class FixTenantDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenancy:fix-database {tenant_id? : The UUID of the tenant to fix (optional - fixes all if not provided)} {--hostname= : Specific hostname to fix}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix tenant database user, password, database, and permissions after PostgreSQL upgrade';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->argument('tenant_id');
        $hostname = $this->option('hostname');

        if ($hostname) {
            // Fix by hostname
            $hostnameModel = \Hyn\Tenancy\Models\Hostname::where('fqdn', $hostname)->first();
            if (!$hostnameModel || !$hostnameModel->website) {
                $this->error("Hostname {$hostname} not found or has no associated website!");
                return 1;
            }
            $this->fixTenant($hostnameModel->website);
        } elseif ($tenantId) {
            // Fix specific tenant by UUID
            $website = Website::where('uuid', $tenantId)->first();
            if (!$website) {
                $this->error("Tenant with UUID {$tenantId} not found!");
                return 1;
            }

            // Check if this website has hostnames
            if ($website->hostnames()->count() === 0) {
                $this->warn("Website {$tenantId} has no hostnames associated with it!");
                $this->line("This might be a duplicate or orphaned record.");

                if ($this->confirm('Do you want to continue anyway?')) {
                    $this->fixTenant($website);
                } else {
                    $this->info("Skipped tenant {$tenantId}");
                    return 0;
                }
            } else {
                $this->fixTenant($website);
            }
        } else {
            // Fix all tenants
            $websites = Website::with('hostnames')->get();
            if ($websites->isEmpty()) {
                $this->info("No tenants found.");
                return 0;
            }

            // Filter out websites without hostnames
            $validWebsites = $websites->filter(function($website) {
                return $website->hostnames()->count() > 0;
            });

            $orphanedWebsites = $websites->filter(function($website) {
                return $website->hostnames()->count() === 0;
            });

            if ($orphanedWebsites->count() > 0) {
                $this->warn("Found " . $orphanedWebsites->count() . " websites without hostnames (possibly duplicates):");
                foreach($orphanedWebsites as $orphaned) {
                    $this->line("  - UUID: {$orphaned->uuid} (ID: {$orphaned->id})");
                }

                if ($this->confirm('Do you want to clean up these orphaned websites?')) {
                    $this->cleanupOrphanedWebsites($orphanedWebsites);
                }
            }

            $this->info("Found " . $validWebsites->count() . " valid tenants. Processing...");
            foreach ($validWebsites as $website) {
                $this->fixTenant($website);
                $this->line(''); // Add spacing between tenants
            }
        }

        $this->info("🎉 All done!");
        return 0;
    }

    /**
     * Clean up orphaned websites (websites without hostnames)
     */
    private function cleanupOrphanedWebsites($orphanedWebsites)
    {
        foreach($orphanedWebsites as $website) {
            $this->line("🗑️  Cleaning up orphaned website: {$website->uuid}");

            try {
                // Drop the database if it exists
                DB::connection('system')->statement("DROP DATABASE IF EXISTS \"{$website->uuid}\"");
                $this->line("   ✅ Dropped database: {$website->uuid}");

                // Drop the user if it exists
                DB::connection('system')->statement("DROP USER IF EXISTS \"{$website->uuid}\"");
                $this->line("   ✅ Dropped user: {$website->uuid}");

                // Delete the website record
                $website->delete();
                $this->line("   ✅ Deleted website record");

            } catch (Exception $e) {
                $this->error("   ❌ Failed to cleanup {$website->uuid}: " . $e->getMessage());
            }
        }
    }

    /**
     * Fix a specific tenant
     */
    private function fixTenant(Website $website)
    {
        $tenantId = $website->uuid;
        $this->info("🔧 Processing tenant: {$tenantId}");

        try {
            // Get tenant configuration from Hyn
            $tenancy = app(Environment::class);
            $tenancy->tenant($website);
            $tenantConfig = config('database.connections.tenant');

            $username = $tenantConfig['username'];
            $password = $tenantConfig['password'];
            $database = $tenantConfig['database'];

            $this->line("   Username: {$username}");
            $this->line("   Database: {$database}");
            $this->line("   Password: " . substr($password, 0, 8) . "...");

            // Step 1: Create/Update User
            $this->createOrUpdateUser($username, $password);

            // Step 2: Create Database if needed
            $this->createDatabase($database, $username);

            // Step 3: Grant Permissions
            $this->grantPermissions($database, $username);

            // Step 4: Test Connection
            $this->testTenantConnection($website);

            $this->info("✅ Tenant {$tenantId} fixed successfully!");

        } catch (Exception $e) {
            $this->error("❌ Failed to fix tenant {$tenantId}: " . $e->getMessage());
        }
    }

    /**
     * Create or update the database user
     */
    private function createOrUpdateUser($username, $password)
    {
        try {
            $systemDb = DB::connection('system');

            // Check if user exists
            $userExists = $systemDb->select("SELECT 1 FROM pg_roles WHERE rolname = ?", [$username]);

            if (!empty($userExists)) {
                $this->line("   👤 User exists, updating password...");
                $systemDb->statement("ALTER USER \"{$username}\" WITH PASSWORD '{$password}' CREATEDB LOGIN");
            } else {
                $this->line("   👤 Creating new user...");
                $systemDb->statement("CREATE USER \"{$username}\" WITH PASSWORD '{$password}' CREATEDB LOGIN");
            }

            $this->info("   ✅ User created/updated");

        } catch (Exception $e) {
            throw new Exception("Failed to create/update user: " . $e->getMessage());
        }
    }

    /**
     * Create the tenant database if it doesn't exist
     */
    private function createDatabase($database, $username)
    {
        try {
            $systemDb = DB::connection('system');

            // Check if database exists
            $dbExists = $systemDb->select("SELECT 1 FROM pg_database WHERE datname = ?", [$database]);

            if (empty($dbExists)) {
                $this->line("   🗄️  Creating database: {$database}");
                $systemDb->statement("CREATE DATABASE \"{$database}\" OWNER \"{$username}\"");
            } else {
                $this->line("   🗄️  Database exists, ensuring ownership...");
                $systemDb->statement("ALTER DATABASE \"{$database}\" OWNER TO \"{$username}\"");
            }

            $this->info("   ✅ Database ready");

        } catch (Exception $e) {
            throw new Exception("Failed to create/update database: " . $e->getMessage());
        }
    }

    /**
     * Grant all necessary permissions
     */
    private function grantPermissions($database, $username)
    {
        try {
            $this->line("   🔐 Granting permissions...");

            // Create a connection to the tenant database as superuser
            $adminConfig = [
                'driver' => 'pgsql',
                'host' => config('database.connections.system.host'),
                'port' => config('database.connections.system.port'),
                'database' => $database, // Connect to tenant database
                'username' => config('database.connections.system.username'),
                'password' => config('database.connections.system.password'),
                'charset' => 'utf8',
                'prefix' => '',
                'schema' => 'public',
                'sslmode' => 'prefer',
            ];

            config(['database.connections.tenant_admin' => $adminConfig]);
            DB::purge('tenant_admin');
            $adminDb = DB::connection('tenant_admin');

            // Nuclear approach: Drop and recreate public schema with proper ownership
            try {
                $this->line("   🏗️  Rebuilding public schema...");
                $adminDb->statement("DROP SCHEMA IF EXISTS public CASCADE");
                $adminDb->statement("CREATE SCHEMA public AUTHORIZATION \"{$username}\"");
                $this->line("   ✅ Public schema rebuilt with proper ownership");
            } catch (Exception $e) {
                $this->line("   ⚠️  Could not rebuild schema, trying alternative approach...");
                // Fallback: ensure schema exists and try to change ownership
                $adminDb->statement("CREATE SCHEMA IF NOT EXISTS public");
                $adminDb->statement("ALTER SCHEMA public OWNER TO \"{$username}\"");
            }

            // Grant explicit permissions
            $adminDb->statement("GRANT ALL ON SCHEMA public TO \"{$username}\"");
            $adminDb->statement("GRANT ALL ON SCHEMA public TO PUBLIC"); // Ensure public role has access too

            // Grant permissions on all existing objects
            $adminDb->statement("GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO \"{$username}\"");
            $adminDb->statement("GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO \"{$username}\"");
            $adminDb->statement("GRANT ALL PRIVILEGES ON ALL FUNCTIONS IN SCHEMA public TO \"{$username}\"");

            // Set default privileges for future objects (set these before making user owner)
            $adminDb->statement("ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO \"{$username}\"");
            $adminDb->statement("ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO \"{$username}\"");
            $adminDb->statement("ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON FUNCTIONS TO \"{$username}\"");

            // Make the user owner of the database (this gives full control)
            DB::connection('system')->statement("ALTER DATABASE \"{$database}\" OWNER TO \"{$username}\"");

            // Test table creation as the tenant user
            $this->line("   🧪 Testing table creation permissions...");
            try {
                $adminDb->statement("SET ROLE \"{$username}\"");
                $adminDb->statement("CREATE TABLE permission_test (id serial primary key, test varchar(50))");
                $adminDb->statement("DROP TABLE permission_test");
                $adminDb->statement("RESET ROLE");
                $this->info("   ✅ Table creation test successful!");
            } catch (Exception $e) {
                $adminDb->statement("RESET ROLE"); // Make sure we reset role even on error
                throw new Exception("Table creation test failed: " . $e->getMessage());
            }

            $this->info("   ✅ Permissions granted and verified");

        } catch (Exception $e) {
            throw new Exception("Failed to grant permissions: " . $e->getMessage());
        }
    }

    /**
     * Test the tenant connection
     */
    private function testTenantConnection(Website $website)
    {
        try {
            $this->line("   🧪 Testing connection...");

            // Switch to tenant context
            $tenancy = app(Environment::class);
            $tenancy->tenant($website);

            // Test basic connection
            $result = DB::connection('tenant')->select('SELECT 1 as test');
            if (empty($result) || $result[0]->test !== 1) {
                throw new Exception("Basic connection test failed");
            }

            // Test table access
            $userCount = DB::connection('tenant')->table('users')->count();
            $this->line("   📊 Found {$userCount} users in tenant database");

            // Test settings table (from the original error)
            try {
                $setting = DB::connection('tenant')->table('settings')
                    ->where('key', 'manager_language')
                    ->first();
                $this->line("   ⚙️  Settings table accessible");
            } catch (Exception $e) {
                $this->line("   ⚠️  Settings table not found (might be normal for new tenants)");
            }

            // Test table creation permissions
            try {
                $testResult = DB::connection('tenant')->select("SELECT has_schema_privilege(?, 'public', 'CREATE') as can_create", [$website->uuid]);
                if ($testResult && $testResult[0]->can_create) {
                    $this->line("   ✅ CREATE permission verified");
                } else {
                    $this->line("   ⚠️  CREATE permission might be limited");
                }
            } catch (Exception $e) {
                $this->line("   ⚠️  Could not verify CREATE permissions");
            }

            $this->info("   ✅ Connection test passed");

        } catch (Exception $e) {
            throw new Exception("Connection test failed: " . $e->getMessage());
        }
    }
}
