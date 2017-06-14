<?php

namespace App\Modules\Attendance\Models;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'name', 'email', 'password', 'phone_number', 'avatar','gender','qualification','description','device_id','device_token','device_type','status'
    ];
    protected $table = 'teachers';
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