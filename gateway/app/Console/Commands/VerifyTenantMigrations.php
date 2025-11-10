<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VerifyTenantMigrations extends Command
{
    protected $signature = 'tenants:verify-migrations {--fix : Run migrations on tenants that need it}';
    protected $description = 'Verify that all tenant databases have migrations run and fix if needed';

    public function handle()
    {
        $fix = $this->option('fix');

        $this->info('Verifying tenant database migrations...');
        $this->info('========================================');
        $this->newLine();

        $tenants = Tenant::with('domains')->get();
        $this->info("Found {$tenants->count()} tenants to check");
        $this->newLine();

        $needsMigration = 0;
        $upToDate = 0;
        $errors = 0;

        foreach ($tenants as $tenant) {
            $domain = $tenant->primary_domain ?? $tenant->domains->first();
            $domainName = $domain?->domain ?? 'NO-DOMAIN';

            $this->info("Checking tenant: {$tenant->id} ({$domainName})");

            try {
                // Initialize tenant context
                tenancy()->initialize($tenant);

                // Check if migrations table exists
                if (!Schema::hasTable('migrations')) {
                    $this->warn("  ❌ Migrations table doesn't exist");
                    $needsMigration++;

                    if ($fix) {
                        $this->info("  🔧 Running migrations...");
                        Artisan::call('migrate', [
                            '--database' => 'tenant',
                            '--path' => 'database/migrations/tenant',
                            '--force' => true
                        ]);
                        $this->info("  ✅ Migrations completed");
                    }
                } else {
                    // Check if settings table has correct schema
                    if (!Schema::hasTable('settings')) {
                        $this->warn("  ❌ Settings table doesn't exist");
                        $needsMigration++;

                        if ($fix) {
                            $this->info("  🔧 Running migrations...");
                            Artisan::call('migrate', [
                                '--database' => 'tenant',
                                '--path' => 'database/migrations/tenant',
                                '--force' => true
                            ]);
                            $this->info("  ✅ Migrations completed");
                        }
                    } elseif (!Schema::hasColumn('settings', 'key')) {
                        $this->warn("  ❌ Settings table missing 'key' column");
                        $needsMigration++;

                        if ($fix) {
                            $this->info("  🔧 Running migrations...");
                            Artisan::call('migrate', [
                                '--database' => 'tenant',
                                '--path' => 'database/migrations/tenant',
                                '--force' => true
                            ]);
                            $this->info("  ✅ Migrations completed");
                        }
                    } else {
                        // Check migration status
                        $migrations = DB::table('migrations')->count();
                        $this->info("  ✅ Up to date ({$migrations} migrations run)");
                        $upToDate++;
                    }
                }

                // End tenant context
                tenancy()->end();

            } catch (\Exception $e) {
                $this->error("  ❌ ERROR: {$e->getMessage()}");
                $errors++;
                tenancy()->end();
            }

            $this->newLine();
        }

        $this->info('========================================');
        $this->info('Summary:');
        $this->info("  ✅ Up to date: {$upToDate}");
        $this->info("  ⚠️  Needs migration: {$needsMigration}");
        $this->info("  ❌ Errors: {$errors}");
        $this->newLine();

        if ($needsMigration > 0 && !$fix) {
            $this->warn('⚠️  Run with --fix to automatically run migrations on tenants that need it');
            $this->info('Command: php artisan tenants:verify-migrations --fix');
        }

        return $errors > 0 ? 1 : 0;
    }
}
