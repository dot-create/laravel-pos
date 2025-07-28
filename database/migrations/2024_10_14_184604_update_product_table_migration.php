<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateProductTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('lead_days')->nullable();
            $table->string('product_weight')->nullable();
            $table->string('net_table')->nullable();
            $table->string('stock_type')->nullable();
            $table->text('item_notes')->nullable();
            $table->string('shipping_way')->nullable();
            $table->double('destination_tax')->nullable();
            $table->double('total_cost')->nullable();
            $table->double('estimated_supplier_freight')->nullable();
            $table->double('purchase_site_commission')->nullable();
            $table->double('purchasing_weight')->nullable();
            $table->double('reorder_quantity')->nullable();
            $table->double('international_supplier_tax')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('supplier_id');
            $table->dropColumn('lead_days');
            $table->dropColumn('product_weight');
            $table->dropColumn('net_table');
            $table->dropColumn('stock_type');
            $table->dropColumn('item_notes');
            $table->dropColumn('shipping_way');
            $table->dropColumn('destination_tax');
            $table->dropColumn('total_cost');
            $table->dropColumn('estimated_supplier_freight');
            $table->dropColumn('purchase_site_commission');
            $table->dropColumn('purchasing_weight');
            $table->dropColumn('reorder_quantity');
            $table->dropColumn('international_supplier_tax');
        });
    }
}
