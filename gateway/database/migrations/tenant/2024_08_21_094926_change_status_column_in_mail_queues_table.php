<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Assuming you are altering a table named 'mail_queues'
        DB::statement('ALTER TABLE mail_queues ALTER COLUMN st TYPE INTEGER USING st::integer');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Change the column back to the original type, if necessary
        // For example, if it was a string:
        DB::statement('ALTER TABLE mail_queues ALTER COLUMN st TYPE VARCHAR(255) USING st::varchar');
    }
};
