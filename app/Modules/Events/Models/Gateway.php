<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class Gateway extends Model
{
    protected $fillable = ['name', 'floorplan_id', 'major_id', 'minor_id', 'uuid', 'gateway_X', 'gateway_Y', 'display_X', 'display_Y', 'status'];
    protected $table    = 'gateway';
    //protected $connection = 'ips_mysql';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }
}
