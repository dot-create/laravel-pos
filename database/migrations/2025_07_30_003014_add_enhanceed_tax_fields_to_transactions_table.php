<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnhanceedTaxFieldsToTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Add new tax calculation fields
            $table->decimal('amount_before_tax', 22, 4)->nullable()->after('final_total');
            $table->enum('tax_type', ['percentage', 'fixed'])->default('percentage')->after('amount_before_tax');
            $table->decimal('tax_value', 22, 4)->nullable()->after('tax_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['amount_before_tax', 'tax_type', 'tax_value']);
        });
    }
}
