<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCourierSaasPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courier_saas_packages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('courier_saas_order_id')->nullable();
            $table->integer('package_id')->nullable();
            $table->string('barcode', 191)->nullable();
            $table->integer('user_id')->nullable();
            $table->string('type', 191)->default('package');
            $table->unsignedInteger('added_by');
            $table->integer('pin_code')->nullable();
            $table->integer('status')->nullable();
            $table->text('comment')->nullable();
            $table->tinyInteger('payment_status')->default(1);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('type');
            $table->index('user_id');
            $table->index('status');
            $table->index('courier_saas_order_id');
            $table->index('package_id');
            $table->index('barcode');

            // Foreign Key Constraints
            $table->foreign('courier_saas_order_id')->references('id')->on('courier_saas_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('courier_saas_packages');
    }
}
