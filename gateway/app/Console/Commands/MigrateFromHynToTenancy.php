<?php

namespace App\Console\Commands;

use App\Models\Domain;
use App\Models\Tenant;
use App\Models\Website;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MigrateFromHynToTenancy extends Command
{
    protected $signature = 'migrate:hyn-to-tenancy {--dry-run : Run without making changes} {--force : Force migration even if tenant exists}';
    protected $description = 'Migrate from Hyn Multi-Tenant to Tenancy for Laravel';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('Starting migration from Hyn to Tenancy for Laravel...');
        $this->info('========================================');

        // Get all Hyn websites with their hostnames
        $websites = Website::with('hostnames')->get();

        $this->info("Found {$websites->count()} websites to migrate");
        $this->newLine();

        $migrated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($websites as $website) {
            $this->info("Processing website: {$website->uuid}");

            try {
                // Check if tenant already exists
                $existingTenant = Tenant::find($website->uuid);

                if ($existingTenant && !$force) {
                    $this->warn("  ⏩ Tenant already exists: {$website->uuid} - SKIPPED");
                    $this->info("     Use --force to re-migrate this tenant");
                    $skipped++;
                    $this->newLine();
                    continue;
                }

                if ($existingTenant && $force) {
                    $this->warn("  🔄 Tenant exists but forcing re-migration");
                }

                if (!$dryRun) {
                    DB::beginTransaction();

                    $hostname_data = $website->hostnames->first();
                    $custom_filed_array = json_decode($hostname_data?->custom_fields, true) ?? [];
                    $email = $website->uuid . '@example.com';

                    // Create or update tenant
                    $tenant = Tenant::updateOrCreate(
                        ['id' => $website->uuid],
                        [
                            'email' => $email,
                            'configure' => $website->configure,
                            'supplier' => $website->supplier,
                            'external' => $website->external,
                            'data' => $custom_filed_array
                        ]
                    );

                    $this->info("  ✅ Tenant created/updated: {$tenant->id}");

                    // Create domains for this tenant
                    $domainCount = 0;
                    foreach ($website->hostnames as $hostname) {
                        $domain = Domain::updateOrCreate(
                            [
                                'domain' => $hostname->fqdn,
                                'tenant_id' => $tenant->id
                            ],
                            [
                                'logo' => $hostname->logo,
                                'configure' => $hostname->configure,
                                'is_primary' => (bool)$hostname->primary,
                                'host_id' => $hostname->host_id ?? null,
                                'custom_fields' => $hostname->custom_fields ?? null,
                            ]
                        );

                        $this->info("     ➕ Domain added: {$hostname->fqdn}" . ($domain->is_primary ? ' (PRIMARY)' : ''));
                        $domainCount++;
                    }

                    // Verify migration
                    $verifyTenant = Tenant::with('domains')->find($tenant->id);
                    if (!$verifyTenant) {
                        throw new \Exception("Verification failed: Tenant not found after creation");
                    }

                    if ($verifyTenant->domains->count() !== $domainCount) {
                        throw new \Exception("Verification failed: Domain count mismatch");
                    }

                    DB::commit();
                    $this->info("  ✅ Migration verified successfully");
                    $migrated++;

                } else {
                    $this->info("  [DRY RUN] Would create tenant: {$website->uuid}");
                    foreach ($website->hostnames as $hostname) {
                        $this->info("  [DRY RUN] Would add domain: {$hostname->fqdn}");
                    }
                }

                $this->newLine();

            } catch (\Exception $e) {
                if (!$dryRun) {
                    DB::rollBack();
                }
                $this->error("  ❌ ERROR migrating {$website->uuid}: {$e->getMessage()}");
                $this->error("     " . $e->getFile() . ':' . $e->getLine());
                $errors++;
                $this->newLine();
            }
        }

        $this->info('========================================');
        $this->info('Migration Summary:');
        $this->info("  ✅ Migrated: {$migrated}");
        $this->info("  ⏩ Skipped: {$skipped}");
        $this->info("  ❌ Errors: {$errors}");
        $this->newLine();

        // Verification check
        if (!$dryRun && $migrated > 0) {
            $this->info('Running verification checks...');
            $tenantCount = Tenant::count();
            $domainCount = Domain::count();
            $this->info("  Total Tenants in new system: {$tenantCount}");
            $this->info("  Total Domains in new system: {$domainCount}");

            // Check for tenants without domains
            $tenantsWithoutDomains = Tenant::doesntHave('domains')->count();
            if ($tenantsWithoutDomains > 0) {
                $this->warn("  ⚠️  Warning: {$tenantsWithoutDomains} tenants have no domains!");
            }

            // Check for domains without primary
            $tenantsWithoutPrimary = Tenant::whereDoesntHave('domains', function ($query) {
                $query->where('is_primary', true);
            })->count();

            if ($tenantsWithoutPrimary > 0) {
                $this->warn("  ⚠️  Warning: {$tenantsWithoutPrimary} tenants have no primary domain!");
            }
        }

        $this->info('========================================');
        $this->info('Migration completed!');

        if (!$dryRun && $migrated > 0) {
            $this->newLine();
            $this->warn('⚠️  IMPORTANT: Next Steps Required');
            $this->warn('================================');
            $this->info('1. Install Passport OAuth clients for all tenants:');
            $this->info('   php artisan tenants:passport-install');
            $this->newLine();
            $this->info('2. Verify tenant databases have been migrated:');
            $this->info('   php artisan tenants:verify-migrations');
            $this->newLine();
        }

        return $errors > 0 ? 1 : 0;
    }
}
