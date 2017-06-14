<?php

namespace App\Modules\Attendance\Models;

use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
	protected $fillable = ['name', 'iot_id', 'device_id'];
	protected $table    = 'class';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_attendance_database_conn');
    }
}
