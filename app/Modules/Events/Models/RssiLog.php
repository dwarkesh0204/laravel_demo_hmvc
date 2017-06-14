<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class RssiLog extends Model
{
    protected $fillable = ['calibration_id', 'timestamp', 'beacon_minor', 'distance_from_cbp', 'rssi','filtered_rssi', 'device_type'];
	protected $table    = 'rssi_log';
	protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }
}
