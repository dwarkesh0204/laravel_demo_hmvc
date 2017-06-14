<?php

namespace App\Modules\Attendance\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Projects;
use Config;

class Course extends Model
{
	protected $fillable = ['name', 'description'];
	protected $table    = 'course';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_attendance_database_conn');
    }
}
