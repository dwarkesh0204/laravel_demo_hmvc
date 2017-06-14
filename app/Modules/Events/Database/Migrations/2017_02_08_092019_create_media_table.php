<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('media', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_id');
            $table->integer('sessions_id');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('thumb_file_path');
            $table->string('file_type');
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
        Schema::drop('media');
    }
}
