<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDatesToPrecinctPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('precinct_packages', function (Blueprint $table) {
            $table->timestamp('sent_at')->nullable()->after('payment_status');
            $table->timestamp('accepted_at')->nullable()->after('sent_at');
            $table->timestamp('arrived_at')->nullable()->after('accepted_at');
            $table->timestamp('delivered_at')->nullable()->after('arrived_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('precinct_packages', function (Blueprint $table) {
            $table->dropColumn('sent_at');
            $table->dropColumn('accepted_at');
            $table->dropColumn('arrived_at');
            $table->dropColumn('delivered_at');
        });
    }
}
