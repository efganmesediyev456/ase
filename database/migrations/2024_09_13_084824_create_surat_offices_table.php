<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSuratOfficesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('surat_offices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('city_id')->nullable();
            $table->unsignedInteger('foreign_id')->nullable();

            $table->string('name', 191)->unique();
            $table->string('name_en', 191)->nullable();

            $table->text('description')->nullable();
            $table->text('description_en')->nullable();

            $table->string('address', 191)->nullable();
            $table->text('address_en')->nullable();

            $table->string('longitude', 255)->nullable();
            $table->string('latitude', 255)->nullable();

            $table->string('contact_phone', 191)->nullable();
            $table->string('contact_name', 191)->nullable();

            $table->time('monday_opening_time')->nullable();
            $table->time('monday_closing_time')->nullable();
            $table->time('tuesday_opening_time')->nullable();
            $table->time('tuesday_closing_time')->nullable();
            $table->time('wednesday_opening_time')->nullable();
            $table->time('wednesday_closing_time')->nullable();
            $table->time('thursday_opening_time')->nullable();
            $table->time('thursday_closing_time')->nullable();
            $table->time('friday_opening_time')->nullable();
            $table->time('friday_closing_time')->nullable();
            $table->time('saturday_opening_time')->nullable();
            $table->time('saturday_closing_time')->nullable();
            $table->time('sunday_opening_time')->nullable();
            $table->time('sunday_closing_time')->nullable();

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
        Schema::dropIfExists('surat_offices');
    }
}
