<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryPriceCurFiledToTracksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tracks', function (Blueprint $table) {
            $table->unsignedInteger('azeriexpress_office_id')->after('store_status')->nullable();
            $table->unsignedInteger('azerpost_office_id')->after('azeriexpress_office_id')->nullable();
            $table->string('delivery_price_cur')->after('delivery_price')->nullable();
            $table->timestamp('customs_declare_at')->after('scanned_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tracks', function (Blueprint $table) {
            $table->dropColumn('delivery_price_cur');
            $table->dropColumn('customs_declare_at');
            $table->dropColumn('azeriexpress_office_id');
            $table->dropColumn('`azerpost_office_id`');
        });
    }
}
