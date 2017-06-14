<?php

namespace App\Modules\Proximity\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleManager extends Model
{
    protected $fillable = ['ads_manager_id', 'title', 'beacon_id', 'date','start_time', 'end_time','status'];
	protected $table    = 'schedule_managers';
	protected $connection;

	public function __construct() {
        /*$pid = \Session::get('project_conn_id');
        \Session::get('project_conn'.$pid);
        $this -> connection = \Session::get('project_conn'.$pid);*/
        $this -> connection = \Session::get('mysql_proximity_database_conn');
    }
}
