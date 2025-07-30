<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsInContactPeopleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contact_people', function (Blueprint $table) {
            $table->string('representative_position')->nullable()->after('representative_name');
            $table->string('representative_mobile')->nullable()->after('representative_phone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contact_people', function (Blueprint $table) {
            $table->dropColumn('representative_position');
            $table->dropColumn('representative_mobile');
        });
    }
}
