<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIpsNodeRelationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('node_relation', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('node_id');
            $table->integer('rel_node_id');
            $table->float('distance',10,2);
            $table->integer('floorplan_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('node_relation');
    }
}
