<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('iot', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('device_id');
            $table->string('type');
            $table->string('gateway_ssid');
            $table->string('gateway_password');
            $table->string('wifi_name');
            $table->string('wifi_password');
            $table->string('mac_id');
            $table->string('major_id');
            $table->string('minor_id');
            $table->string('location');
            $table->string('placeId');
            $table->string('iot_X');
            $table->string('iot_Y');
            $table->string('uuid');
            $table->string('beaconName');
            $table->string('advertiseId');
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
        Schema::drop('iot');
    }
}
