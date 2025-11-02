<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('contract_nr', 50)->nullable()->index();
            $table->dropForeign('contracts_hostname_id_foreign');
            $table->dropForeign('contracts_company_id_foreign');
            $table->unsignedBigInteger('company_id')->nullable()->change();
            $table->renameColumn('hostname_id', 'receiver_id'); // 90
            $table->string('receiver_type')->default('App\Models\Hostname');
            $table->string('receiver_connection')->default('cec');
            $table->renameColumn('company_id', 'requester_id');
            $table->string('requester_type')->default('App\Models\Company');
            $table->string('requester_connection')->default('cec');
            $table->string('type')->default('external');
            $table->date('start_at')->nullable();
            $table->date('end_at')->nullable();
            $table->unsignedBigInteger('blueprint_id')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('contract_nr');
            $table->renameColumn('receiver_id','hostname_id');
            $table->renameColumn('requester_id','company_id');
            $table->dropColumn('receiver_type');
            $table->dropColumn('requester_type');
            $table->foreign('company_id')->on('companies')->references('id')->onDelete('cascade');
            $table->foreign('hostname_id')->on('hostnames')->references('id')->onDelete('cascade');
            $table->dropColumn('start_at');
            $table->dropColumn('end_at');
            $table->dropColumn('blueprint_id');
            $table->dropColumn('type');
            $table->dropColumn('requester_connection');
            $table->dropColumn('receiver_connection');
        });
    }
};
