<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGatewayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gateway', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('type',50);
            $table->integer('floorplan_id');
            $table->string('major_id');
            $table->string('minor_id');
            $table->string('uuid');
            $table->string('gateway_X');
            $table->string('gateway_Y');
            $table->string('display_X');
            $table->string('display_Y');
            $table->integer('status');
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
        Schema::drop('gateway');
    }
}
