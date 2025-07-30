<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCustomerRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('contact_person_id')->nullable()->after('business_id'); 

            $table->foreign('contact_person_id')
                ->references('id')
                ->on('contact_people')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_requests', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['contact_person_id']);

            // Then drop the columns
            $table->dropColumn([ 'contact_person_id' ]);
        });
    }
}
