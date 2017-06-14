<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class Poi extends Model
{
    protected $fillable = ['name', 'area', 'floorplan_id', 'connect', 'poi_X', 'poi_Y', 'display_X', 'display_Y', 'node_id', 'status'];
    protected $table    = 'poi';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }
}
