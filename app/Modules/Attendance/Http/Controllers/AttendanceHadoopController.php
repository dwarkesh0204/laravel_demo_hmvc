<?php

namespace App\Modules\Attendance\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Attendance\Models\Attendancehadoop;

class AttendanceHadoopController extends Controller
{
    public function __construct(){
        $this->AttendancehadoopModel = new Attendancehadoop();
    }

    public function attendance_log(Request $request)
    {
        $postdata = file_get_contents('php://input');
        $split = explode(':', $postdata,2); //split from : with 2 limited partitions
        $file = public_path('request.log');
        \File::put($file, $postdata);
        $deviceId = $split[0];
        $data_arr =  explode("|",$split[1]);//second partition all data from the device id
        return $this->AttendancehadoopModel->SetAttendanceLog($deviceId,$data_arr);
    }
}
