<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSuratOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('surat_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('user_sent_id')->nullable();
            $table->unsignedInteger('surat_office_id');
            $table->string('name', 191)->nullable();
            $table->string('barcode', 191)->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->text('details')->nullable();
            $table->tinyInteger('is_paid')->default(1);
            $table->integer('status')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('surat_orders');
    }
}
