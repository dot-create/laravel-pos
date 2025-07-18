<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToInvoiceSchemesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoice_schemes', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive'])->default('active')->after('is_default');
            $table->integer('end_number')->nullable()->after('start_number');
            $table->date('start_date')->nullable()->after('end_number');
            $table->date('expiration_date')->nullable()->after('start_date');
            $table->string('invoicing_key')->nullable()->after('expiration_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoice_schemes', function (Blueprint $table) {
            $table->dropColumn(['status', 'end_number', 'start_date', 'expiration_date', 'invoicing_key']);
        });
    }
}
