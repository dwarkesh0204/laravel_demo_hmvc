<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class Beacon extends Model
{
    protected $fillable = ['name', 'type', 'area', 'floorplan_id', 'project_id', 'macid', 'major_id', 'minor_id', 'uuid', 'beacon_X', 'beacon_Y', 'display_X', 'display_Y', 'node_id', 'status'];
    protected $table    = 'beacon';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }
}
