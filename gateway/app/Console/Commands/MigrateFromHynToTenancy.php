<?php

namespace App\Console\Commands;

use App\Models\Domain;
use App\Models\Tenant;
use App\Models\Website;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MigrateFromHynToTenancy extends Command
{
    protected $signature = 'migrate:hyn-to-tenancy {--dry-run : Run without making changes}';
    protected $description = 'Migrate from Hyn Multi-Tenant to Tenancy for Laravel';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info('Starting migration from Hyn to Tenancy for Laravel...');

        // Get all Hyn websites with their hostnames
        $websites = Website::with('hostnames')->get();

        $this->info("Found {$websites->count()} websites to migrate");

        foreach ($websites as $website) {
            $this->info("Processing website: {$website->uuid}");

            if (!$dryRun) {
                $hostname_data = $website->hostnames->first();
                $custom_filed = json_decode($hostname_data?->custom_fields);

                $custom_filed_array = json_decode($hostname_data?->custom_fields, true);
                $email = $website->uuid. '@example.com';

//                dd(json_decode($website->hostnames->first()?->custom_fields, true), true);
                // Create new tenant
                $tenant = Tenant::create([
                    'id' => $website->uuid,
                    'email' => $email,
                    'configure' => $website->configure,
                    'supplier' => $website->supplier,
                    'external'  => $website->external,
                    'data' => $custom_filed_array
                ]);
                // Create domains for this tenant
                foreach ($website->hostnames as $hostname) {
                    Domain::create([
                        'domain' => $hostname->fqdn,
                        'tenant_id' => $tenant->id,
                        'logo' => $tenant->logo,
                        'configure' => $tenant->configure,
                        'is_primary' => (bool)$tenant->primary,
                    ]);

                    $this->info("  - Added domain: {$hostname->fqdn}");
                }
            } else {
                $this->info("  [DRY RUN] Would create tenant: {$website->uuid}");
                foreach ($website->hostnames as $hostname) {
                    $this->info("  [DRY RUN] Would add domain: {$hostname->fqdn}");
                }
            }
        }

        $this->info('Migration completed!');
    }
}
