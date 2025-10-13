<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaymentStatusFieldToAzeriExpressPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('azeri_express_packages', function (Blueprint $table) {
            $table->boolean('payment_status')->default(true)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('azeri_express_packages', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });
    }
}
