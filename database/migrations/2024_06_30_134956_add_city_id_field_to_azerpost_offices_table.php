<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCityIdFieldToAzerpostOfficesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('azerpost_offices', function (Blueprint $table) {
            $table->bigInteger('city_id')->after('foreign_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('azerpost_offices', function (Blueprint $table) {
            $table->dropColumn('city_id');
        });
    }
}
