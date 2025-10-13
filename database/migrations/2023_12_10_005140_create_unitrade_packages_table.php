<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnitradePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unitrade_packages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('package_id')->nullable();
            $table->integer('track_id')->nullable();
            $table->string('uid')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('customer_id')->nullable();
            $table->enum('type', ['ozon'])->nullable();

            $table->string('package_code', 30)->nullable();
            $table->string('delivery_number')->nullable();

            $table->integer('warehouse_id')->nullable();
            $table->text('comment')->nullable();
            $table->integer('is_liquid')->nullable();
            $table->integer('is_door')->nullable();

            $table->string('seller_name')->nullable();
            $table->string('seller_email')->nullable();
            $table->string('seller_phone')->nullable();
            $table->string('seller_address')->nullable();
            $table->string('seller_ioss_number')->nullable();
            $table->string('seller_country')->nullable();
            $table->string('seller_city')->nullable();

            $table->string('buyer_city')->nullable();
            $table->string('buyer_country')->nullable();
            $table->string('buyer_phone_number')->nullable();
            $table->string('buyer_email_address')->nullable();
            $table->string('buyer_first_name')->nullable();
            $table->string('buyer_last_name')->nullable();
            $table->string('buyer_zip_code')->nullable();
            $table->string('buyer_pin_code')->nullable();
            $table->string('buyer_region')->nullable();
            $table->text('buyer_shipping_address')->nullable();
            $table->text('buyer_billing_address')->nullable();

            $table->string('invoice_currency', 50)->nullable();
            $table->float('invoice_price', 10, 2)->nullable();
            $table->string('invoice_due_date')->nullable();
            $table->string('invoice_url')->nullable();

            $table->float('shipping_invoice_price', 10, 2)->nullable();
            $table->string('shipping_invoice_due')->nullable();
            $table->string('shipping_invoice_url')->nullable();
            $table->string('shipping_currency', 50)->nullable();
            $table->decimal('shipping_cost', 10, 2)->default('0.00');

            $table->text('request_json')->nullable();
            $table->integer('status')->nullable();
            $table->decimal('weight', 10, 2)->default(0);

            $table->timestamps();

            $table->index('delivery_number');
            $table->index(['delivery_number', 'package_code']);
            $table->index(['delivery_number', 'type']);
            $table->index('id');
            $table->index('package_id');
            $table->index('package_code');
            $table->index('user_id');
            $table->index('type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('unitrade_packages');
    }
}
