<?php

namespace App\Modules\Ips\Models;

use Illuminate\Database\Eloquent\Model;

class Beacon extends Model
{
    protected $fillable = ['name', 'type', 'area', 'floorplan_id', 'major_id', 'minor_id', 'uuid', 'macid', 'beacon_X', 'beacon_Y', 'display_X', 'display_Y', 'node_id', 'status'];
    protected $table    = 'beacon';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_ips_database_conn');
    }
}
