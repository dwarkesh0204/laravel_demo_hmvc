<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('menu', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cat_id');
            $table->string('title');
            $table->string('descriptions');
            $table->string('view_name');
            $table->integer('parent');
            $table->integer('default_page');
            $table->integer('status');
            $table->integer('order');
            $table->string('icon');
            $table->integer('login_requuired');
            $table->string('screens');
            $table->string('extra_data');
            $table->integer('display_icon');
            $table->string('cmsPage');
            $table->string('webLink');
            $table->string('HTMLContent');
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
        Schema::drop('menu');
    }
}
