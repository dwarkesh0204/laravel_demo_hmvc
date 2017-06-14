<?php

namespace App\Modules\Attendance\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Projects;
use Config;

class Iot extends Model
{
    protected $fillable = ['title', 'type','device_id', 'created_at', 'updated_at'];
	protected $table    = 'iot';
	protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_attendance_database_conn');
    }
}
