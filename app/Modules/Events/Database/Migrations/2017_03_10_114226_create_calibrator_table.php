<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCalibratorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calibrator', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('floorplan_id');
            $table->string('node_X');
            $table->string('node_Y');
            $table->string('display_X');
            $table->string('display_Y');
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
        Schema::drop('calibrator');
    }
}
