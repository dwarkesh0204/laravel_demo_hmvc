<?php

namespace App\Modules\Attendance\Models;

use Illuminate\Database\Eloquent\Model;

class Lectures extends Model
{
	protected $fillable = ['course', 'semester', 'device_id', 'subject', 'class', 'starttime', 'endtime','entry_via','teacherid'];
	protected $table    = 'lectures';
	protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_attendance_database_conn');
    }

}
