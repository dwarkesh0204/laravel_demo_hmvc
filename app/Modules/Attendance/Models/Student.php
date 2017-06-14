<?php

namespace App\Modules\Attendance\Models;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
 
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = ['name', 'email', 'courseid','semesterid','phone_number', 'roll_no','device_id','device_type','device_token','mac_address','minor_id','major_id'];

    protected $table = 'students';
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_attendance_database_conn');
    }
}