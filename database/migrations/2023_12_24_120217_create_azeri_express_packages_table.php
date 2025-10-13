<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAzeriExpressPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('azeri_express_packages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('azeri_express_order_id')->nullable();
            $table->integer('package_id')->nullable();
            $table->string('barcode')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('type')->default('package');
            $table->unsignedInteger('added_by');
            $table->integer('pin_code')->nullable();
            $table->integer('status')->nullable();
            $table->text('comment')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('azeri_express_order_id')->references('id')->on('azeri_express_orders');
            $table->index('type');
            $table->index('user_id');
            $table->index('status');
            $table->index('azeri_express_order_id');
            $table->index('package_id');
            $table->index('barcode');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('azeri_express_packages');
    }
}
