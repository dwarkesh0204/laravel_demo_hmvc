<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLecturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('lectures', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('course');
            $table->integer('semester');
            $table->string('device_id');
            $table->integer('subject');
            $table->integer('class');
            $table->dateTime('starttime');
            $table->dateTime('endtime');
            $table->string('entry_via');
            $table->integer('teacherid');
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
        //
        Schema::drop('lectures');
    }
}
