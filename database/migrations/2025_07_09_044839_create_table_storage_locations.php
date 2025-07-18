<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableStorageLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('storage_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id')->unsigned();
            $table->integer('location_id')->unsigned();
            $table->string('rack');
            $table->string('row');
            $table->string('position');
            $table->timestamps();
        });

        // Modify 'product_racks' table
        Schema::table('product_racks', function (Blueprint $table) {
            // Remove rack, row, and position columns if they exist
            $table->dropColumn(['rack', 'row', 'position']);

            // Add foreign key to 'storage_locations'
            $table->string('storage_location_id')->nullable()->after('product_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop 'storage_locations' table
        Schema::dropIfExists('storage_locations');

        // Revert changes in 'product_racks' table
        Schema::table('product_racks', function (Blueprint $table) {
            // Drop the new column
            $table->dropColumn('storage_location_id');

            // Add back the old columns
            $table->string('rack');
            $table->string('row');
            $table->string('position');
        });

    }
}
