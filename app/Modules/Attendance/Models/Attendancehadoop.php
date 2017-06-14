<?php

namespace  App\Modules\Attendance\Models;

use App\Models\Project_Devices;
use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Http\Request;
use App\Models\Projects;
use Config;
use Illuminate\Support\Facades\Session;

class Attendancehadoop extends Model
{
    protected $hadoop_db_conn;
    protected $connection;
    public $JsonArray   = array();

    public function __construct()
    {
        $postdata = file_get_contents('php://input');
        $split = explode(':', $postdata,2); //split from : with 2 limited partitions
        $Project_deviceId = $split[0];
        $project = Project_Devices::where('device_id',$Project_deviceId)->select('project_id')->first();
        $project_conn_secret = $project->project_id;
        if($project_conn_secret != '')
        {
            $data = Projects::where('id',trim($project_conn_secret))->first();
            if(count($data))
            {
                $schemaName = $data->db;
                $secret = $data->db_user;
                $password = \Crypt::decrypt($data->db_password);

                Config::set('database.connections.'.$schemaName, array(
                    'driver'    => 'mysql',
                    'host'      => 'localhost',
                    'database'  => $schemaName,
                    'username'  => $secret,
                    'password'  => $password,
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix'    => '',
                ));
                $project_conn = 'project_conn'.$data->id;
                Session::forget($project_conn);
                Session::set('project_conn_id',$data->id);
                Session::set($project_conn,$schemaName);
                Session::set('mysql_attendance_database_conn',$schemaName);
                $this->connection = $schemaName;
                $this->hadoop_db_conn = $schemaName;
            }
            else
            {
                $this->JsonArray['data'] = '';
                $this->JsonArray['message'] = "Wrong Connection for Application please check your Secret Token.";
                $this->JsonArray['status_code'] = 204;
                header("content-type: application/json");
                echo json_encode($this->JsonArray);exit;
            }
        }
        else
        {
            $this->JsonArray['data'] = '';
            $this->JsonArray['message'] = "Wrong Connection for Application please check your Secret Token.";
            $this->JsonArray['status_code'] = 204;
            header("content-type: application/json");
            echo json_encode($this->JsonArray);exit;
        }
    }
    public function SetAttendanceLog($deviceId,$data_arr)
    {
        $count = count($data_arr);
        $insert_arr = array();
        $test_insert_arr = array();
        if($count > 0)
        {
            $timestamp = date('Y-m-d H:i:s');

            $query = Lectures::select('lectures.id','course.name as course_name','course.id as course_id','semester.name as semester_name','semester.id as semester_id','class.name as class_name','class.id as class_id','lectures.device_id as device_id','subject.name as subject_name','subject.id as subject_id','lectures.starttime','lectures.endtime');
            $query->leftjoin('course','course.id','=','lectures.course');
            $query->leftjoin('semester','semester.id','=','lectures.semester');
            $query->leftjoin('class','class.id','=','lectures.class');
            $query->leftjoin('subject','subject.id','=','lectures.subject');
            $query->where('lectures.starttime','<=',$timestamp);
            $query->where('lectures.endtime','>=',$timestamp);
            $query->where('lectures.device_id',$deviceId);
            $lecture = $query->first();

            if(count($lecture) > 0)
            {
                $lecture_id = $lecture->id;
                $course_name = $lecture->course_name;
                $course_id = $lecture->course_id;
                $semester_name = $lecture->semester_name;
                $semester_id = $lecture->semester_id;
                $class_name = $lecture->class_name;
                $class_id = $lecture->class_id;
                $device_id = $lecture->device_id;
                $subject_name = $lecture->subject_name;
                $subject_id = $lecture->subject_id;
                $starttime = $lecture->starttime;
                $endtime = $lecture->endtime;
                $teacher_id = $lecture->teacherid;
                $present = 'n';

                for($i=0;$i<$count;$i++)
                {
                    if($data_arr[$i] != '')
                    {
                        $subdata_arr = explode(':', $data_arr[$i],2);//to get ble or wifi type
                        $beacon_type = $subdata_arr[0]; //beacon type
                        $beacon_data = $subdata_arr[1]; //beacon data

                        switch($beacon_type)
                        {
                            case 'ble':
                                $beacon_data_arr = explode(',',$beacon_data);
                                foreach ($beacon_data_arr as $index => $line)
                                {
                                    if($line != '')
                                    {
                                        $line_arr = explode(':',$line);
                                        if(count($line_arr) > 0)
                                        {
                                            $factory_id = ($line_arr[0] != '')?$line_arr[0]:'';
                                            $beacon_id = ($line_arr[1] != '')?$line_arr[1]:'';
                                            $hexaData = $this->getParts($line_arr[2],array(4,4,2));//major minor measured_power
                                            $major = ($hexaData[0] != '')?hexdec($hexaData[0]):'';
                                            $minor = ($hexaData[1] != '')?hexdec($hexaData[1]):'';
                                            $measured_power = ($hexaData[2] != '')?hexdec($hexaData[2]):'';
                                            $beacon_mac = ($line_arr[3] != '')?$line_arr[3]:'';
                                            $rssi = ($line_arr[4] != '')?hexdec($line_arr[4]):'';
                                            //echo $beacon_type .'<----->'. $beacon_mac .'<----->'. $device_id .'<----->'. $rssi .'<----->'. $factory_id .'<----->'. $major .'<----->'. $minor .'<----->'. $measured_power ;exit;
                                            if($beacon_type != '' && $beacon_mac != '' && $device_id != '' && $rssi != ''  && $factory_id != ''  && $major != ''  && $minor != ''  )
                                            {
                                                $insert_arr[] = "('".$beacon_mac."','".$device_id."','".$minor."','".$major."','".$timestamp."','".$rssi."','".$factory_id."','".$beacon_id."','".$measured_power."','".$beacon_type."','".$lecture_id."','".$course_name."','".$semester_name."','".$class_name."','".$subject_name."','".$starttime."','".$endtime."','".$course_id."','".$semester_id."','".$class_id."','".$subject_id."','".$teacher_id."',$present)";
                                            }
                                        }
                                    }
                                }
                                break;
                            case 'wifi':
                                $beacon_data_arr = explode(',',$beacon_data);
                                foreach ($beacon_data_arr as $index => $line)
                                {
                                    if($line != '')
                                    {
                                        $line_arr = explode(':',$line);
                                        if(count($line_arr) > 0)
                                        {
                                            $factory_id = '';
                                            $beacon_id = '';
                                            $major = '';
                                            $minor = '';
                                            $measured_power = '';
                                            $beacon_mac = ($line_arr[1] != '')?$line_arr[1]:'';
                                            $rssi = ($line_arr[2] != '')?$line_arr[2]:'';

                                            if($beacon_mac != '' && $rssi != '')
                                            {
                                                $insert_arr[] = "('".$beacon_mac."','".$device_id."','".$minor."','".$major."','".$timestamp."','".$rssi."','".$factory_id."','".$beacon_id."','".$measured_power."','".$beacon_type."','".$lecture_id."','".$course_name."','".$semester_name."','".$class_name."','".$subject_name."','".$starttime."','".$endtime."','".$course_id."','".$semester_id."','".$class_id."','".$subject_id."','".$teacher_id."',$present)";
                                                $test_insert_arr[] = "('".$beacon_mac."','".$device_id."','".$timestamp."','".abs($rssi)."')";
                                            }
                                        }
                                    }
                                }
                                break;
                        }
                        /*INSERT INTO TABLE indolytics_saas.iot_logs (mac_address varchar(20),device_id STRING,minor_id varchar(5) ,major_id varchar(5), logged_timestamp string,rssi varchar(5),factory_id varchar(25),ibeacon_id string,measured_power varchar(10),type varchar(20),lecture_id INT,course_name string,semester_name string,class_name string,subject_name string,starttime string,endtime  string
) CLUSTERED BY (device_id) into 2 buckets ROW FORMAT DELIMITED FIELDS TERMINATED BY ',' STORED as orc tblproperties('transactional'='true');

                        ALTER TABLE iot_logs ADD COLUMNS (course_id int,semester_id int,class_id int,subject_id int);
                        ALTER TABLE iot_logs ADD COLUMNS (teacher_app varchar(1))
                        */
                    }
                }
                if(count($insert_arr)>0)
                {
                    $all_data = implode(',',$insert_arr);
                   // $insert_data = DB::connection('odbc')->statement("INSERT INTO TABLE ".$this->hadoop_db_conn.".iot_logs VALUES ".$all_data );
                    if($test_insert_arr)
                    {
                        $test_all_data = implode(',',$test_insert_arr);
                        $insert_data = DB::connection('odbc')->statement("INSERT INTO TABLE test_logs.test_iot_logs VALUES ".$test_all_data );
                    }
                }
            }
        }
    }
    public function getParts($string, $positions)
    {
        $parts = array();
        foreach ($positions as $position){
            $parts[] = substr($string, 0, $position);
            $string = substr($string, $position);
        }
        return $parts;
    }

}
