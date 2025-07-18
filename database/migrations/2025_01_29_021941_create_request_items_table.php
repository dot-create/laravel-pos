<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('customer_requests')->onDelete('cascade');
            $table->integer('product_id');
            $table->integer('variation_id');
            $table->integer('quantity');
            $table->string('status')->default('Pending');
            $table->integer('supplier1_id')->nullable();
            $table->string('quantity_supplier1')->nullable();
            $table->string('unit_price_supplier1')->nullable();
            $table->string('freight_supplier1')->nullable();
            $table->string('ecom_fee_percentage_supplier1')->nullable();
            $table->string('formula_price_supplier1')->nullable();
            $table->integer('supplier2_id')->nullable();
            $table->string('quantity_supplier2')->nullable();
            $table->string('unit_price_supplier2')->nullable();
            $table->string('freight_supplier2')->nullable();
            $table->string('ecom_fee_percentage_supplier2')->nullable();
            $table->string('formula_price_supplier2')->nullable();
            $table->integer('supplier3_id')->nullable();
            $table->string('quantity_supplier3')->nullable();
            $table->string('unit_price_supplier3')->nullable();
            $table->string('freight_supplier3')->nullable();
            $table->string('ecom_fee_percentage_supplier3')->nullable();
            $table->string('formula_price_supplier3')->nullable();
            $table->integer('supplier4_id')->nullable();
            $table->string('quantity_supplier4')->nullable();
            $table->string('unit_price_supplier4')->nullable();
            $table->string('freight_supplier4')->nullable();
            $table->string('ecom_fee_percentage_supplier4')->nullable();
            $table->string('formula_price_supplier4')->nullable();
            $table->boolean('is_best_supplier1')->default(0);
            $table->boolean('is_best_supplier2')->default(0);
            $table->boolean('is_best_supplier3')->default(0);
            $table->boolean('is_best_supplier4')->default(0);
            $table->string('product_link')->nullable();
            $table->string('weight_unit')->nullable();
            $table->string('destination_tax')->nullable();
            $table->string('item_notes')->nullable();
            $table->string('sell_price_wot')->nullable();
            $table->string('purchase_weight')->nullable();
            $table->string('shipping_way')->nullable();
            $table->string('delivery_time')->nullable();
            $table->string('supply_ref')->nullable();
            $table->string('suggested_sell_price_USD_wot')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_items');
    }
}
