<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEnFiledsToAzerpostOfficesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('azerpost_offices', function (Blueprint $table) {
            $table->string('name_en')->nullable();
            $table->text('description_en')->after('name_en');
            $table->text('address_en')->after('description_en');
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
            $table->dropColumn('name_en');
            $table->dropColumn('description_en');
            $table->dropColumn('address_en');
        });
    }
}
