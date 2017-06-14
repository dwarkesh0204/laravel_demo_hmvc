<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePoiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('poi', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('area');
            $table->integer('floorplan_id');
            $table->integer('connect');
            $table->string('poi_X');
            $table->string('poi_Y');
            $table->string('display_X');
            $table->string('display_Y');
            $table->integer('node_id');
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
        Schema::drop('poi');
    }
}
