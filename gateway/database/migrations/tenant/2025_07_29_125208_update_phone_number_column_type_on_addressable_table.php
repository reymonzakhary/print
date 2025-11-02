<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
  public function up(): void
    {
        DB::statement("
          UPDATE addressable
          SET phone_number = NULL
          WHERE phone_number IS NULL
             OR phone_number = ''
             OR phone_number !~ '^[0-9]+$'");


        DB::statement("
            ALTER TABLE addressable
            ALTER COLUMN phone_number TYPE BIGINT
            USING CASE
                WHEN phone_number ~ '^[0-9]+$' THEN phone_number::bigint
                ELSE 0
            END
        ");

        DB::statement("
            ALTER TABLE addressable
            ADD CONSTRAINT addressable_phone_number_unsigned
            CHECK (phone_number >= 0)
        ");
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
          ALTER TABLE addressable
          DROP CONSTRAINT IF EXISTS addressable_phone_number_unsigned");

        // Convert back to varchar
        DB::statement("
            ALTER TABLE addressable
            ALTER COLUMN phone_number TYPE VARCHAR(255)
            USING phone_number::varchar
        ");
    }
};
