<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fields', function (Blueprint $table) {
            $table->increments('id');
            $table->string('display_name');
            $table->string('field_name');
            $table->string('field_type');
            $table->longText('items');
            $table->string('default');
            $table->string('validation_rule');
            $table->integer('multiple');
            $table->integer('active');
            $table->integer('required');
            $table->integer('searchable');
            $table->integer('filterable');
            $table->integer('privacy');
            $table->string('type');
            $table->integer('group_id');
            $table->integer('order');
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
        Schema::drop('fields');
    }
}
