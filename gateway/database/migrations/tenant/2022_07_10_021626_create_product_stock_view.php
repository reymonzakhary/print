<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateProductStockView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE VIEW product_stock_view AS
                SELECT
                    skus.product_id AS product_id,
                    products.variation AS variation,
                    skus.id AS sku_id,
                    skus.sku AS sku,
                    COALESCE( SUM(stocks.qty) - COALESCE( SUM(order_items.qty) ,0 ) ,0) AS stock,
                    case when COALESCE( SUM(stocks.qty) - COALESCE( SUM(order_items.qty) ,0 ) ,0) > 0
                        then true
                        else false
                    end in_stock
                FROM skus
                LEFT JOIN (
                    SELECT
                        stocks.sku_id AS id,
                        SUM(stocks.qty) AS qty
                    FROM stocks
                    GROUP BY stocks.sku_id
                ) AS stocks using (id)
                LEFT JOIN (
                    SELECT
                        order_items.sku_id AS id,
                        SUM(order_items.qty) AS qty
                    FROM order_items
                    GROUP BY order_items.sku_id
                ) AS order_items USING (id)
                LEFT JOIN (
                    SELECT
                        products.id AS id,
                        products.variation AS variation
                    FROM products
                    GROUP BY products.id
                ) AS products USING (id)
                LEFT JOIN products AS p
                    ON CAST(skus.product_id AS BIGINT) = p.id
                    WHERE p.stock_product = true
                GROUP BY skus.id, products.variation
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS product_stock_view");
    }
}
