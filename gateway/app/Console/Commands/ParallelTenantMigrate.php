<?php

namespace App\Console\Commands;

use App\Models\Website;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ParallelTenantMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenancy:parallel-migrate {--workers=10} {--timeout=300}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run tenant migrations in parallel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting parallel tenant migrations...');

        $tenants = Website::all()->pluck('id')->toArray();
        $totalTenants = count($tenants);

        if ($totalTenants === 0) {
            $this->info('No tenants found.');
            return 0;
        }

        $this->info("Found {$totalTenants} tenants to migrate");

        $workers = $this->option('workers');
        $chunks = array_chunk($tenants, $workers);
        $completed = 0;
        $failed = [];

        $progressBar = $this->output->createProgressBar($totalTenants);
        $progressBar->start();

        foreach ($chunks as $chunk) {
            $processes = [];

            // Start processes for this chunk
            foreach ($chunk as $tenantId) {
                $process = new Process([
                    'php', 'artisan', 'tenancy:migrate',
                    '--force', '--website_id=' . $tenantId
                ]);

                $process->setTimeout($this->option('timeout'));
                $process->start();
                $processes[$tenantId] = $process;
            }

            // Wait for all processes in this chunk to complete
            foreach ($processes as $tenantId => $process) {
                $process->wait();

                if ($process->isSuccessful()) {
                    $completed++;
                } else {
                    $failed[] = [
                        'tenant' => $tenantId,
                        'error' => $process->getErrorOutput() ?: $process->getOutput()
                    ];
                }

                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Migration completed: {$completed}/{$totalTenants} successful");

        if (!empty($failed)) {
            $this->error("Failed migrations:");
            foreach ($failed as $failure) {
                $this->error("- Tenant {$failure['tenant']}: {$failure['error']}");
            }
            return 1;
        }

        $this->info('All tenant migrations completed successfully!');
        return 0;
    }
}
