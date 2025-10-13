<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAzerpostPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('azerpost_packages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('azerpost_order_id')->nullable();
            $table->string('package_id');
            $table->string('foreign_order_id')->nullable();
            $table->string('type');
            $table->string('barcode')->unique();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('added_by')->nullable();
            $table->string('pin_code')->nullable();
            $table->string('status');
            $table->text('comment')->nullable();
            $table->string('payment_status');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('azerpost_order_id')->references('id')->on('azerpost_orders')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('azerpost_packages');
    }
}
