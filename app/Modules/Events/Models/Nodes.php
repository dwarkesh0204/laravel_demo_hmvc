<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class Nodes extends Model
{
    protected $fillable = ['id', 'name', 'type', 'floorplan_id', 'node_X', 'node_Y', 'display_X', 'display_Y', 'relation'];
    protected $table    = 'nodes';
    //protected $connection = 'ips_mysql';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }
}
