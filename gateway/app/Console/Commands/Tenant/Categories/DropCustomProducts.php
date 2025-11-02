<?php

namespace App\Console\Commands\Tenant\Categories;

use App\Models\Website;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DropCustomProducts extends Command
{

    protected array $tables = [
        'media',
        'types',
        'stocks',
        'locations',
        'warehouses',
        'cart_variation',
        'carts',
        'skus',
        'variations',
        'products',
        'options',
        'boxes',
        'categories',
        'brands',
        'units'
    ];

    protected array $migrations = [
        '2020_07_22_121532_create_media_table',
        '2022_05_19_153450_create_types_table',
        '2022_07_10_020930_add_sku_id_to_order_items_table',
        '2022_07_10_021626_create_product_stock_view',
        '2022_04_20_210422_create_units_table',
        '2022_04_20_210423_create_brands_table',
        '2022_04_20_210424_create_categories_table',
        '2022_04_20_215219_create_boxes_table',
        '2022_04_20_215307_create_options_table',
        '2022_04_20_215347_create_products_table',
        '2022_04_20_215500_create_variations_table',
        '2022_04_20_215832_create_skus_table',
        '2022_04_20_215833_create_warehouses_table',
        '2022_04_20_215834_create_locations_table',
        '2022_04_20_215904_create_stocks_table',
        '2022_04_20_215905_create_carts_table',
        '2022_04_20_215906_create_cart_variation_table',
        '2022_05_10_152750_add_row_id_column_to_categories_table',
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'drop:tables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop all custom products tables';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Execute the console command.
     *
     * @return Collection
     */
    public function handle()
    {
        if (
            Str::lower((env('APP_ENV'))) === 'local'
        ) {
            $this->dropMigration();

        } elseif ($this->confirm('Are you want to run modules seed on ' . env('APP_ENV'), false)) {
            $this->dropMigration();
        }
    }

    public function dropMigration(): Collection
    {


        return collect($this->tables)->map(function ($table) {
            collect(Website::all('uuid')->toArray())->map(function ($website) use ($table) {
                DB::setDefaultConnection('tenant');
                Config::set('database.connections.tenant.database', $website['uuid']);
                DB::reconnect('tenant');

                Schema::disableForeignKeyConstraints();

                DB::statement("DROP VIEW IF EXISTS product_stock_view");

                if (Schema::hasColumn('order_items', 'sku_id')) {
                    Schema::table('order_items', function (Blueprint $table) {
                        $table->dropColumn('sku_id');
                    });
                }

                Schema::dropIfExists($table);
                Schema::enableForeignKeyConstraints();
                DB::table('migrations')->whereIn('migration', $this->migrations)->delete();
                $this->info("{$table} has been dropped successfully");
            });
        });
    }
}
