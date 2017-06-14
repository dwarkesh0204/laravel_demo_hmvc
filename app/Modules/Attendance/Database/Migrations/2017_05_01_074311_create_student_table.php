<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('students', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('avatar');
            $table->integer('courseid');
            $table->integer('semesterid');
            $table->string('phone_number');
            $table->string('roll_no');
            $table->string('mac_address');
            $table->string('device_id');
            $table->string('device_type');
            $table->string('device_token');
            $table->string('minor_id');
            $table->string('major_id');
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
        Schema::drop('students');
    }
}
