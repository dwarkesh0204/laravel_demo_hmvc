<?php

namespace App\Modules\Beaconmanager\Models;

use Illuminate\Database\Eloquent\Model;

class Beacon extends Model
{
    protected $fillable = ['name', 'type', 'area', 'floorplan_id', 'major_id', 'minor_id', 'uuid', 'location', 'placeId', 'beacon_X', 'beacon_Y', 'display_X', 'display_Y', 'node_id', 'status', 'beaconName','advertiseId'];
    protected $table    = 'beacon';
    protected $connection;
    public function __construct() {

        $this -> connection = \Session::get('mysql_bm_database_conn');
    }
}
