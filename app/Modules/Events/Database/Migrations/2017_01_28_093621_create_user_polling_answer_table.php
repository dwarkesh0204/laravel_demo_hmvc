<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserPollingAnswerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_polling_answer', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('session_id');
            $table->integer('poll_question_id');
            $table->integer('poll_answer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_polling_answer');
    }
}
