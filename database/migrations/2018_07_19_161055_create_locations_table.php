<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('postcode');
            //should probably be nullable
            $table->time('open_Monday');
            $table->time('open_Tuesday');
            $table->time('open_Wednesday');
            $table->time('open_Thursday');
            $table->time('open_Friday');
            $table->time('open_Saturday');
            $table->time('open_Sunday');
            $table->time('closed_Monday');
            $table->time('closed_Tuesday');
            $table->time('closed_Wednesday');
            $table->time('closed_Thursday');
            $table->time('closed_Friday');
            $table->time('closed_Saturday');
            $table->time('closed_Sunday');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('locations');
    }
}
