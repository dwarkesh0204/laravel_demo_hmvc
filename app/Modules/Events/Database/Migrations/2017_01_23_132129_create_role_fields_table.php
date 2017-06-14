<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoleFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('role_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('role_id');
            $table->string('display_name');
            $table->string('field_name');
            $table->string('field_type');
            $table->string('items');
            $table->integer('multiple');
            $table->integer('active');
            $table->integer('required');
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
        Schema::drop('role_fields');
    }
}
