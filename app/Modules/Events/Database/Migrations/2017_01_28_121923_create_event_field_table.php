<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventFieldTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('event_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_id');
            $table->string('display_name');
            $table->string('field_name');
            $table->string('field_type');
            $table->string('items');
            $table->integer('multiple');
            $table->integer('active');
            $table->integer('required');
            $table->dateTime('updated_at');
            $table->dateTime('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::drop('event_fields');
    }
}
