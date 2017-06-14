<?php

namespace  App\Modules\Visitors\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Http\Request;

use App\Models\Projects;
use Config;
use Illuminate\Support\Facades\Session;

class Webservice extends Model
{
    protected $hadoop_db_conn;
    protected $connection;
    public $JsonArray   = array();

    public function __construct()
    {
        $Data = json_decode(stripslashes(htmlspecialchars_decode(urldecode(\Request::input('reqObject')))));
        $TaskData = $Data->taskData;
        $project_conn_secret = $TaskData->secret_token;
        if($project_conn_secret != '')
        {
            $data = Projects::where('secret',trim($project_conn_secret))->first();
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
                Session::set('project_conn_id',$data->id);
                Session::set($project_conn,$schemaName);
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

    /**
     * Use To Register User.
     *
     * Request Object :
     * {"task":"register","taskData":{"name":"rahul","email":"rahul@tasolglobal.com","course":"PGDM-c","phone_number":"9898900885","device_id":"device id", "device_type":"device type", "device_token":"forpush", "roll_no":"1" , "mac_address":"11AVD","semester":"semester"}}
     *
     */
    public function register($TaskData)
    {

        $input = array();
        $input['roll_no'] = $TaskData->roll_no;
        $input['name'] = $TaskData->name;
        $input['phone_number'] = $TaskData->phone_number;
        $input['course'] = $TaskData->course;
        $input['email'] = $TaskData->email;
        $input['semester'] = $TaskData->semester;
        $input['mac_address'] = $TaskData->mac_address;
        /*$input['password']     = Hash::make($TaskData->password);*/

        $input['device_id'] = $TaskData->device_id;
        $input['device_type'] = $TaskData->device_type;
        $input['device_token'] = $TaskData->device_token;

        $res = $this->checkMinorMajor(rand(1, 9999),rand(1, 9999));

        $input['minor_id'] = $res['minor'];
        $input['major_id'] = $res['major'];
        $token = "";

        if (!empty($TaskData->device_token)) {
            // if already same device token inserted in database then it's update blank
            Student::where('device_token', '=', $TaskData->device_token)->update((array('device_token' => "")));
        }

        if (!empty($TaskData->email)) {
            $studentDetail = Student::where('email',$TaskData->email)->first();
            if(count($studentDetail) > 0)
            {
                /*$this->JsonArray['message'] = 'Email Already Exist.';
                $this->JsonArray['status_code'] = 702;*/
                $student_data = array(
                    'id' => $studentDetail->id,
                    'roll_no' => $studentDetail->roll_no,
                    'name' => $studentDetail->name,
                    'email' => $studentDetail->email,
                    'phone_number' => $studentDetail->phone_number,
                    'course' => $studentDetail->course,
                    'semester' => $studentDetail->semester,
                    'minor_id' => $studentDetail->minor_id,
                    'major_id' => $studentDetail->major_id,
                );

                $this->JsonArray['message'] = 'Registration Successfull.';
                $this->JsonArray['student_id'] = $studentDetail->id;
                $this->JsonArray['student_data'] = $student_data;
                $this->JsonArray['status_code'] = 200;
                $this->JsonArray['token'] = $token;
                header("content-type: application/json");
                echo json_encode($this->JsonArray);exit;
            }
        }

        $student = new Student();
        $student->roll_no = $input['roll_no'];
        $student->name = $input['name'];
        $student->email = $input['email'];
        $student->phone_number = $input['phone_number'];
        $student->course = $input['course'];
        $student->semester = $input['semester'];
        $student->minor_id = $input['minor_id'];
        $student->major_id = $input['major_id'];
        $student->mac_address = $input['mac_address'];
        $student->device_id = $input['device_id'];
        $student->device_type = $input['device_type'];
        $student->device_token = $input['device_token'];
        $student->save();

        $studentDetail = Student::findOrFail($student->id);

        $student_data = array(
            'id' => $studentDetail->id,
            'roll_no' => $studentDetail->roll_no,
            'name' => $studentDetail->name,
            'email' => $studentDetail->email,
            'phone_number' => $studentDetail->phone_number,
            'course' => $studentDetail->course,
            'semester' => $studentDetail->semester,
            'minor_id' => $studentDetail->minor_id,
            'major_id' => $studentDetail->major_id,
        );

        $this->JsonArray['message'] = 'Registration Successfull.';
        $this->JsonArray['student_id'] = $studentDetail->id;
        $this->JsonArray['student_data'] = $student_data;
        $this->JsonArray['status_code'] = 200;
        $this->JsonArray['token'] = $token;
        header("content-type: application/json");
        echo json_encode($this->JsonArray);exit;
    }

    public function notifications($TaskData)
    {
        $query = Notifications::leftjoin('notifications_student','notifications_student.notification_id','=','notifications.id');
        $query->select("notifications.*");
        $query->where('notifications_student.minor_id',$TaskData->minor_id);
        $query->where('notifications_student.major_id',$TaskData->major_id);
        $notifications = $query->paginate(10);

        if (!$notifications->Total())
        {
            $this->JsonArray['data'] = "";
            $this->JsonArray['total'] = $notifications->Total();
            $this->JsonArray['message'] = "No Data available.";
            $this->JsonArray['status_code'] = 204;
        }
        else
        {
            $this->JsonArray['data'] = $notifications;
            $this->JsonArray['total'] = $notifications->Total();
            $this->JsonArray['message'] = "";
            $this->JsonArray['status_code'] = 200;
        }
        header("content-type: application/json");
        echo json_encode($this->JsonArray);exit;
    }


    public function checkMinorMajor($minor,$major)
    {
        $data = Student::where('major_id',$major)->where('minor_id',$minor)->count();
        if($data)
        {
            $minor = rand(1, 9999);
            $major = rand(1, 9999);
            $this->checkMinorMajor(rand(1, 9999),rand(1, 9999));
        }
        else
        {
            return array('minor'=>$minor,'major'=>$major);
        }
    }
    public function attendance($TaskData)
    {
        $get_data = DB::connection('odbc')->select( DB::raw("SELECT * FROM ".$this->hadoop_db_conn.".iot_logs  where minor_id = '".$TaskData->minor_id."' and major_id = '".$TaskData->major_id."' ") );
        //$get_data = DB::connection('odbc')->select( DB::raw("SELECT * FROM ".$this->hadoop_db_conn.".iot_logs  where minor_id = '122' or major_id = '122'") );dd($get_data);
        $subjects = Subject::select('id','name')->get();
        /*where type='ble' and lecture_id in ('11','10','9') and minor_id = '".$TaskData->minor_id."' and major_id = '".$TaskData->major_id."'*/
        $data_collection =array();
        $threshold = 0;//30
        $extra_clause = '';
        $extra_clause_inner = '';
        $timestamp = date('Y-m-d H:i:s');
        $log_coll = array();
        $final_log = array();
        if(count($get_data))
        {
            /*On going lectures check start*/
            $ongoing_lecture = Lectures::where('starttime','<=',$timestamp);
            $ongoing_lecture->where('endtime','>=',$timestamp);
            $ongoing_lectureIDs = $ongoing_lecture->pluck('id')->toArray();

            if(!count($ongoing_lectureIDs))
            {
                $lecture_str = "('".implode("','",$ongoing_lectureIDs)."')";;
                $extra_clause = " and lecture_id not in ".$lecture_str." ";
            }
            /*On going lectures check ends*/
            /*Filter start*/
            if($TaskData->subject)
            {
                $subject_arr =  explode(',',$TaskData->subject);
                $sub_str = "('".implode("','",$subject_arr)."')";
                $extra_clause .= ' and subject_name in '.$sub_str.' ';
            }
            if($TaskData->startdate != '' && $TaskData->enddate != '')
            {
                $fil_startdate = $TaskData->startdate.' 00:00:00';
                $fil_enddate = $TaskData->enddate.' 59:59:59';

                $extra_clause .= ' and starttime >= "'.$fil_startdate.'" and starttime <= "'.$fil_enddate.'"';
            }
            $extra_clause .= " and type='ble' ";
            /*Filter ends*/
            $log_data = DB::connection('odbc')->select( DB::raw("SELECT count(lecture_id) as lecture_tot,lecture_id,subject_name,course_name FROM ".$this->hadoop_db_conn.".iot_logs where minor_id = '".$TaskData->minor_id."' and major_id = '".$TaskData->major_id."' ".$extra_clause ." group by lecture_id,subject_name,course_name"));
            //dd($log_data);
            $log_count = count($log_data);

            if($log_count)
            {
                for($i=0; $i < $log_count ; $i++)
                {
                    if($log_data[$i]->lecture_tot > $threshold)
                    {
                        $index = $log_data[$i]->course_name.'|'.$log_data[$i]->subject_name;
                        if(!array_key_exists($index,$log_coll))
                        {
                            $log_coll[$index] = 1;
                        }
                        else
                        {
                            $log_coll[$index] = $log_coll[$index]+1;
                        }
                    }
                    /*
                    else
                    {
                        $log_data[$i]->present = 0;
                    }*/
                }

                foreach($log_coll as $key => $val)
                {
                    $inner_array = array();
                    $caluse_para = explode('|',$key);
                    $query = Lectures::leftjoin('subject','subject.id','=','lectures.subject');
                    $query->leftjoin('course','course.id','=','lectures.course');
                    if($TaskData->startdate != '' && $TaskData->enddate != '')
                    {
                        $fil_startdate = $TaskData->startdate.' 00:00:00';
                        $fil_enddate = $TaskData->enddate.' 59:59:59';
                        $query->where('starttime','>=',$fil_startdate);
                        $query->where('endtime','<=',$fil_enddate);
                    }
                    if($TaskData->subject)
                    {
                        $query->whereIn('subject.name',explode(',',$TaskData->subject));
                    }
                    $query->where('course.name',$caluse_para[0]);
                    $query->where('subject.name',$caluse_para[1]);
                    $count = $query->count();
                    $inner_array['lecture_name'] = $caluse_para[1];
                    $inner_array['present'] = $val;
                    $inner_array['lectures_count'] = $count;
                    $final_log[] = $inner_array;
                }
            }

            if (!count($final_log))
            {
                $this->JsonArray['data'] = "";
                $this->JsonArray['total'] = count($final_log);
                $this->JsonArray['message'] = "No Data available.";
                $this->JsonArray['subjects']    = $subjects;
                $this->JsonArray['status_code'] = 204;
            }
            else
            {
                $this->JsonArray['data'] = $final_log;
                $this->JsonArray['total'] = count($final_log);
                $this->JsonArray['message'] = "";
                $this->JsonArray['subjects']    = $subjects;
                $this->JsonArray['status_code'] = 200;
            }
        }
        else
        {
            $this->JsonArray['data'] = $final_log;
            $this->JsonArray['total'] = count($final_log);
            $this->JsonArray['message'] = "";
            $this->JsonArray['subjects']    = $subjects;
            $this->JsonArray['status_code'] = 200;
        }

        header("content-type: application/json");
        echo json_encode($this->JsonArray);exit;
    }
    public function registerationparams($TaskData)
    {
        $courses = Course::select('name','id')->get();
        $semesters = Semester::select('name','id')->get();


        if (!count($courses) && !count($semesters))
        {
            $this->JsonArray['data'] = "";
            $this->JsonArray['courses_total'] = count($courses);
            $this->JsonArray['semesters_total'] = count($semesters);
            $this->JsonArray['message'] = "No Data available.";
            $this->JsonArray['status_code'] = 204;
        }
        else
        {
            $this->JsonArray['courses'] = $courses;
            $this->JsonArray['semesters'] = $semesters;
            $this->JsonArray['courses_total'] = count($courses);
            $this->JsonArray['semesters_total'] = count($semesters);
            $this->JsonArray['message'] = "";
            $this->JsonArray['status_code'] = 200;
        }
        header("content-type: application/json");
        echo json_encode($this->JsonArray);exit;
    }
}