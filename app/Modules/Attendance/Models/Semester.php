<?php

namespace App\Modules\Attendance\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Projects;
use Config;

class Semester extends Model
{
	protected $fillable = ['name','courseid','description'];
	protected $table    = 'semester';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_attendance_database_conn');
    }
}
