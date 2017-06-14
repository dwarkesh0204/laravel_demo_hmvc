<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class RssiSession extends Model
{
    protected $fillable = ['floorplan_id', 'name', 'status'];
    protected $table    = 'rssi_session';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }
}
