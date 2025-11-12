<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\ClientRepository;

class TenantPassportInstall extends Command
{
    protected $signature = 'tenants:passport-install {--force : Force reinstall even if clients exist}';
    protected $description = 'Install Passport OAuth clients for all tenant databases';

    public function handle()
    {
        $this->info('Installing Passport clients for all tenants...');
        $this->info('=========================================');

        $force = $this->option('force');
        $tenants = Tenant::with('domains')->get();

        if ($tenants->isEmpty()) {
            $this->error('No tenants found!');
            return 1;
        }

        $this->info("Found {$tenants->count()} tenants");
        $this->newLine();

        $installed = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($tenants as $tenant) {
            $tenantId = $tenant->id;
            $primaryDomain = $tenant->primary_domain?->domain ?? $tenant->domains->first()?->domain ?? 'unknown';

            $this->info("Processing tenant: {$tenantId} ({$primaryDomain})");

            try {
                // Initialize tenancy context
                tenancy()->initialize($tenant);

                // Check if password client already exists
                $existingClient = DB::connection('tenant')
                    ->table('oauth_clients')
                    ->where('password_client', true)
                    ->first();

                if ($existingClient && !$force) {
                    $this->warn("  ⏩ Password client already exists - SKIPPED");
                    $this->info("     Client ID: {$existingClient->id}");
                    $skipped++;
                    tenancy()->end();
                    $this->newLine();
                    continue;
                }

                if ($existingClient && $force) {
                    $this->warn("  🔄 Deleting existing password client (force mode)");
                    DB::connection('tenant')->table('oauth_clients')
                        ->where('id', $existingClient->id)
                        ->delete();

                    // Also delete related personal access client entries
                    DB::connection('tenant')->table('oauth_personal_access_clients')
                        ->where('client_id', $existingClient->id)
                        ->delete();
                }

                // Create password grant client
                $clientRepository = new ClientRepository();
                $client = $clientRepository->createPasswordGrantClient(
                    null,
                    "Tenant {$tenantId} Password Client",
                    'http://localhost'
                );

                $this->info("  ✅ Password client created");
                $this->info("     Client ID: {$client->id}");
                $this->info("     Client Secret: {$client->plainSecret}");
                $installed++;

                // Also create personal access client
                $personalClient = DB::connection('tenant')
                    ->table('oauth_clients')
                    ->where('personal_access_client', true)
                    ->first();

                if (!$personalClient) {
                    $personalClient = $clientRepository->createPersonalAccessClient(
                        null,
                        "Tenant {$tenantId} Personal Access Client",
                        'http://localhost'
                    );

                    DB::connection('tenant')->table('oauth_personal_access_clients')->insert([
                        'client_id' => $personalClient->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $this->info("  ✅ Personal access client created");
                }

                tenancy()->end();
                $this->newLine();

            } catch (\Exception $e) {
                $this->error("  ❌ Error: " . $e->getMessage());
                $errors++;
                tenancy()->end();
                $this->newLine();
                continue;
            }
        }

        // Summary
        $this->info('=========================================');
        $this->info('Migration Summary:');
        $this->info("  ✅ Installed: {$installed}");
        $this->info("  ⏩ Skipped: {$skipped}");
        $this->info("  ❌ Errors: {$errors}");
        $this->info('=========================================');

        if ($errors > 0) {
            $this->warn('Some tenants had errors. Please check the output above.');
            return 1;
        }

        $this->info('✅ All tenants processed successfully!');
        return 0;
    }
}
