<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTrackIdColumnToPackageLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_logs', function (Blueprint $table) {
            $table->unsignedInteger('package_id')->nullable()->change();
            $table->unsignedBigInteger('track_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('package_logs', function (Blueprint $table) {
            $table->unsignedInteger('package_id')->change();
            $table->dropColumn('track_id');
        });
    }
}
