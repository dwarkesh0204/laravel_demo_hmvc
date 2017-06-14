<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoleFieldValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('role_field_values', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('field_id');
            $table->integer('role_id');
            $table->integer('event_id');
            $table->longText('value');
            $table->integer('is_privacy');
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
        Schema::dropIfExists('role_field_values');
    }
}
