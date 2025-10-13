<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrecinctOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('precinct_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('user_sent_id')->nullable();
            $table->unsignedInteger('precinct_office_id');
            $table->string('name')->nullable();
            $table->string('barcode')->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->text('details')->nullable();
            $table->tinyInteger('is_paid')->default(1);
            $table->integer('status')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('precinct_orders');
    }
}
