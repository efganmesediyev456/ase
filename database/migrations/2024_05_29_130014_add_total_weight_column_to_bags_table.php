<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalWeightColumnToBagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('airboxes', function (Blueprint $table) {
            $table->unsignedDecimal('total_weight')->nullable();
            $table->unsignedInteger('total_count')->nullable();
            $table->unsignedInteger('container_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('airboxes', function (Blueprint $table) {
            $table->dropColumn('total_weight');
            $table->dropColumn('total_count');
        });
    }
}
