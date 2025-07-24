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
            $table->unsignedInteger('business_id')->nullable()->after('id');
            $table->unsignedBigInteger('contact_person_id')->nullable()->after('business_id'); // Assuming `id` in contact_people is BIGINT
            $table->string('status')->default('pending')->after('foreign_business_location_id');

            $table->foreign('business_id')
                ->references('id')
                ->on('business')
                ->onDelete('set null');

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
            $table->dropForeign(['business_id']);
            $table->dropForeign(['contact_person_id']);

            // Then drop the columns
            $table->dropColumn(['business_id', 'contact_person_id', 'status']);
        });
    }
}
