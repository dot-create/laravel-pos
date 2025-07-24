<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('customer_requests', function (Blueprint $table) {
            $table->id(); // this is unsignedBigInteger

            $table->unsignedInteger('customer_id'); // ✅ match users.id type
            $table->string('request_reference')->unique();
            $table->integer('business_location_id');
            $table->integer('foreign_business_location_id');
            $table->timestamps();

            $table->foreign('customer_id')
                ->references('id')
                ->on('contacts')
                ->onDelete('cascade');
        });

    }

    public function down()
    {
        Schema::dropIfExists('customer_requests');
    }
}