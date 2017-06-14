<?php
namespace App\Modules\Attendance\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Projects;
use Config;

class NotificationsStudent extends Model
{
    protected $fillable = ['student_id', 'minor_id', 'major_id', 'notification_id'];
    protected $table = 'notifications_student';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_attendance_database_conn');
    }
}
