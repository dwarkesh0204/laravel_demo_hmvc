<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->increments('eventid');
            $table->string('name');
            $table->longText('shortdesc');
            $table->longText('description');
            $table->integer('status');
            $table->date('startdate');
            $table->time('starttime');
            $table->date('enddate');
            $table->time('endtime');
            $table->integer('addedby');
            $table->text('cover_image');
            $table->string('type');
            $table->integer('price');
            $table->integer('expected_attendaees');
            $table->longText('venue');
            $table->string('phone_number', '16');
            $table->string('email');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->integer('entrypass');
            $table->string('pagetype');
            $table->string('url');
            $table->longText('html');
            $table->string('meals');
            $table->string('roles');
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
        Schema::drop('events');
    }
}
