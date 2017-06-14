<?php

namespace App\Modules\Beaconmanager\Models;

use Illuminate\Database\Eloquent\Model;

class Iot extends Model
{
    protected $fillable = ['title', 'type','device_id', 'gateway_ssid', 'gateway_password', 'wifi_name', 'wifi_password', 'major_id', 'minor_id', 'location', 'placeId', 'iot_X', 'iot_Y', 'uuid', 'userid', 'beaconName', 'advertiseId', 'created_at', 'updated_at'];
	protected $table    = 'iot';
    protected $connection;
    public function __construct() {
        $this -> connection = \Session::get('mysql_bm_database_conn');
    }
}
