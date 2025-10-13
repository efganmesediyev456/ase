<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEnFiledsToDeliveryPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_points', function (Blueprint $table) {
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
        Schema::table('delivery_points', function (Blueprint $table) {
            $table->dropColumn('name_en');
            $table->dropColumn('description_en');
            $table->dropColumn('address_en');
        });
    }
}
