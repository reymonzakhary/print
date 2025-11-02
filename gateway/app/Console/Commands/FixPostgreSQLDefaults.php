<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Exception;

class FixPostgreSQLDefaults extends Command
{
    protected $signature = 'postgres:fix-defaults';
    protected $description = 'Fix PostgreSQL 17 default permissions for tenant creation';

    public function handle()
    {
        $this->info("🔧 Fixing PostgreSQL 17 default permissions...");

        try {
            $systemDb = DB::connection('system');

            // Fix template1 permissions
            $template1Config = config('database.connections.system');
            $template1Config['database'] = 'template1';
            config(['database.connections.template1_fix' => $template1Config]);
            DB::purge('template1_fix');

            $template1Db = DB::connection('template1_fix');
            $template1Db->statement("GRANT ALL ON SCHEMA public TO PUBLIC");
            $template1Db->statement("GRANT CREATE ON SCHEMA public TO PUBLIC");
            $template1Db->statement("ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO PUBLIC");
            $template1Db->statement("ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO PUBLIC");

            $this->info("✅ PostgreSQL defaults fixed for new tenant creation!");

        } catch (Exception $e) {
            $this->error("❌ Failed: " . $e->getMessage());
        }
    }
}
