<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAzerpostOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('azerpost_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('user_sent_id')->nullable();
            $table->unsignedInteger('azerpost_office_id');
            $table->string('name');
            $table->string('barcode')->unique();
            $table->decimal('weight', 8, 2);
            $table->text('details')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->string('status');
            $table->timestamp('sent_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('azerpost_office_id')->references('id')->on('azerpost_offices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('azerpost_orders');
    }
}
