<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRssiLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rssi_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('calibration_id');
            $table->string('timestamp');
            $table->string('beacon_minor');
            $table->string('distance_from_cbp');
            $table->string('rssi');
            $table->string('filtered_rssi');
            $table->string('device_type');
            $table->integer('rssi_session_id');
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
        Schema::dropIfExists('rssi_log');
    }
}
