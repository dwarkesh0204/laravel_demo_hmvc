<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleManager extends Model
{
    protected $fillable = ['ads_manager_id', 'title', 'beacon_id', 'date','start_time', 'end_time','status'];
	protected $table    = 'schedule_managers';
	protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }
}
