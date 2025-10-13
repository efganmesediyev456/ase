<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDateWorkingHoursFieldsToDeliveryPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_points', function (Blueprint $table) {
            $table->time('monday_opening_time')->nullable();
            $table->time('monday_closing_time')->nullable();
            $table->time('tuesday_opening_time')->nullable();
            $table->time('tuesday_closing_time')->nullable();
            $table->time('wednesday_opening_time')->nullable();
            $table->time('wednesday_closing_time')->nullable();
            $table->time('thursday_opening_time')->nullable();
            $table->time('thursday_closing_time')->nullable();
            $table->time('friday_opening_time')->nullable();
            $table->time('friday_closing_time')->nullable();
            $table->time('saturday_opening_time')->nullable();
            $table->time('saturday_closing_time')->nullable();
            $table->time('sunday_opening_time')->nullable();
            $table->time('sunday_closing_time')->nullable();
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
            $table->dropColumn('monday_opening_time');
            $table->dropColumn('monday_closing_time');
            $table->dropColumn('tuesday_opening_time');
            $table->dropColumn('tuesday_closing_time');
            $table->dropColumn('wednesday_opening_time');
            $table->dropColumn('wednesday_closing_time');
            $table->dropColumn('thursday_opening_time');
            $table->dropColumn('thursday_closing_time');
            $table->dropColumn('friday_opening_time');
            $table->dropColumn('friday_closing_time');
            $table->dropColumn('saturday_opening_time');
            $table->dropColumn('saturday_closing_time');
            $table->dropColumn('sunday_opening_time');
            $table->dropColumn('sunday_closing_time');
        });
    }
}
