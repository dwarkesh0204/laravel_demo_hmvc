<?php

namespace  App\Modules\Attendance\Models;

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
        $input['courseid'] = $TaskData->course;
        $input['email'] = $TaskData->email;
        $input['semesterid'] = $TaskData->semester;
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

        if (!empty($TaskData->email))
        {
            $student_detail = Student::where('email',$TaskData->email)->first();
            if(count($student_detail) > 0)
            {
                if($student_detail->device_type == 'web')
                {
                    Student::where('id', '=', $student_detail->id)->update(array('device_id'=>$TaskData->device_id,'device_type'=>$TaskData->device_type,'device_token'=>$TaskData->device_token));
                }
                /*$this->JsonArray['message'] = 'Email Already Exist.';
                $this->JsonArray['status_code'] = 702;*/
                $studentDetail = Student::leftjoin('course','course.id','=','students.courseid')->leftjoin('semester','semester.id','=','students.semesterid')->select('students.*','course.name as course_name','semester.name as semester_name')->where('students.id',$student_detail->id)->first();
                $student_data = array(
                    'id' => $studentDetail->id,
                    'roll_no' => $studentDetail->roll_no,
                    'name' => $studentDetail->name,
                    'email' => $studentDetail->email,
                    'phone_number' => $studentDetail->phone_number,
                    'course' => $studentDetail->course_name,
                    'semester' => $studentDetail->semester_name,
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
        $student->courseid = $input['courseid'];
        $student->semesterid = $input['semesterid'];
        $student->minor_id = $input['minor_id'];
        $student->major_id = $input['major_id'];
        $student->mac_address = $input['mac_address'];
        $student->device_id = $input['device_id'];
        $student->device_type = $input['device_type'];
        $student->device_token = $input['device_token'];
        $student->save();

        $studentDetail = $studentDetail = Student::leftjoin('course','course.id','=','students.courseid')->leftjoin('semester','semester.id','=','students.semesterid')->select('students.*','course.name as course_name','semester.name as semester_name')->where('students.id',$student->id)->first();

        $student_data = array(
            'id' => $studentDetail->id,
            'roll_no' => $studentDetail->roll_no,
            'name' => $studentDetail->name,
            'email' => $studentDetail->email,
            'phone_number' => $studentDetail->phone_number,
            'course' => $studentDetail->course_name,
            'semester' => $studentDetail->semester_name,
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

        //$get_data = DB::connection('odbc')->select( DB::raw("SELECT * FROM ".$this->hadoop_db_conn.".iot_logs where type='ble' ") );dd($get_data);
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

            if(count($ongoing_lectureIDs))
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
            //$extra_clause .= " and type='ble' ";
            /*Filter ends*/
            $log_data = DB::connection('odbc')->select( DB::raw("SELECT count(lecture_id) as lecture_tot,lecture_id,subject_name,course_name FROM ".$this->hadoop_db_conn.".iot_logs where minor_id = '".$TaskData->minor_id."' and major_id = '".$TaskData->major_id."' and teacher_app ='n' ".$extra_clause ." group by lecture_id,subject_name,course_name"));
            //dd($log_data);
            $teacher_log_data = DB::connection('odbc')->select( DB::raw("SELECT teacher_app as lecture_tot,lecture_id,subject_name,course_name,teacher_app FROM ".$this->hadoop_db_conn.".iot_logs where minor_id = '".$TaskData->minor_id."' and major_id = '".$TaskData->major_id."' and teacher_app ='y' ".$extra_clause ." group by lecture_id,subject_name,course_name,teacher_app"));

            $teacher_log_count = count($teacher_log_data);
            if($teacher_log_count)
            {
                for($i=0; $i < $teacher_log_count ; $i++)
                {
                    $teacher_log_data[$i]->teacher_tot = 1;
                    $log_data[] = $teacher_log_data[$i];
                }
            }

            $log_count = count($log_data);
            if($log_count)
            {
                for($i=0; $i < $log_count ; $i++)
                {
                    if($log_data[$i]->lecture_tot == 'y')
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
                    else if($log_data[$i]->lecture_tot > $threshold)
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
    public function coursesParams($TaskData)
    {
        $courses = Course::select('name','id')->get();

        if (!count($courses))
        {
            $this->JsonArray['data'] = "";
            $this->JsonArray['courses_total'] = count($courses);
            $this->JsonArray['message'] = "No Data available.";
            $this->JsonArray['status_code'] = 204;
        }
        else
        {
            $this->JsonArray['courses'] = $courses;
            $this->JsonArray['courses_total'] = count($courses);
            $this->JsonArray['message'] = "";
            $this->JsonArray['status_code'] = 200;
        }
        header("content-type: application/json");
        echo json_encode($this->JsonArray);exit;
    }


    public function semestersParams($TaskData)
    {
        $semesters = Semester::select('name','id')->where('courseid',$TaskData->courseid)->get();
        if (!count($semesters))
        {
            $this->JsonArray['data'] = "";
            $this->JsonArray['semesters_total'] = count($semesters);
            $this->JsonArray['message'] = "No Data available.";
            $this->JsonArray['status_code'] = 204;
        }
        else
        {
            $this->JsonArray['semesters'] = $semesters;
            $this->JsonArray['semesters_total'] = count($semesters);
            $this->JsonArray['message'] = "";
            $this->JsonArray['status_code'] = 200;
        }
        header("content-type: application/json");
        echo json_encode($this->JsonArray);exit;
    }
    /**
     * @param $TaskData
     * 192.168.5.155/indolytics_saas/public/attendance/webservice
    Request Object :
     * {"task":"subject_params","taskData":{"courseid":"1","semesterid":"1","secret_token":"86EBDD90"}}
     */
    public function subjectParams($TaskData)
    {
        $subjects = Subject::select('name','id')->where('courseid',$TaskData->courseid)->where('semesterid',$TaskData->semesterid)->get();

        if (!count($subjects))
        {
            $this->JsonArray['subjects'] = "";
            $this->JsonArray['subjects_total'] = count($subjects);
            $this->JsonArray['message'] = "No Data available.";
            $this->JsonArray['status_code'] = 204;
        }
        else
        {
            $this->JsonArray['subjects'] = $subjects;
            $this->JsonArray['subjects_total'] = count($subjects);
            $this->JsonArray['message'] = "";
            $this->JsonArray['status_code'] = 200;
        }
        header("content-type: application/json");
        echo json_encode($this->JsonArray);exit;
    }
    /*Teacher related webservices*/
    /**
     * Use To Login Teacher.
     *
     * Request Object :
     * {"task":"teacher_login","taskData":{"email":"kamlesh@tasolglobal.com","password":"123123","device_id":"device id", "device_type":"device type", "device_token":"forpush","secret_token":"86EBDD90"}}
     *
     */
    public function teacherLogin($TaskData)
    {
        $input = array();
        $input['email'] = $TaskData->email;
        $input['password'] =  \Hash::make($TaskData->password);
        $input['device_id'] = $TaskData->device_id;
        $input['device_type'] = $TaskData->device_type;
        $input['device_token'] = $TaskData->device_token;

        //$res = $this->checkMinorMajor(rand(1, 9999),rand(1, 9999));

        //$input['minor_id'] = $res['minor'];
        //$input['major_id'] = $res['major'];
        $token = "";

        if (!empty($TaskData->email))
        {
            $teacher_detail = Teacher::where('email',$TaskData->email)->first();
            if(count($teacher_detail) > 0)
            {
                Teacher::where('id', '=', $teacher_detail->id)->update(array('device_id'=>$TaskData->device_id,'device_type'=>$TaskData->device_type,'device_token'=>$TaskData->device_token));

                $teacherDetail = Teacher::where('id',$teacher_detail->id)->first();
                $teacher_data = array(
                    'id' => $teacherDetail->id,
                    'avatar' => ($teacherDetail->avatar != '')?asset($teacherDetail->avatar):'',
                    'name' => $teacherDetail->name,
                    'email' => $teacherDetail->email,
                    'phone_number' => $teacherDetail->phone_number,
                    'gender' => $teacherDetail->gender,
                    'qualification' => $teacherDetail->qualification,
                    'description' => $teacherDetail->description,
                );

                $this->JsonArray['message'] = 'Login Successfull.';
                $this->JsonArray['teacher_id'] = $teacherDetail->id;
                $this->JsonArray['teacher_data'] = $teacher_data;
                $this->JsonArray['status_code'] = 200;
                $this->JsonArray['token'] = $token;

                header("content-type: application/json");
                echo json_encode($this->JsonArray);exit;
            }
        }
        $this->JsonArray['message'] = 'Teacher not Registered.';
        $this->JsonArray['status_code'] = 204;
        header("content-type: application/json");
        echo json_encode($this->JsonArray);exit;
    }
    /*Teacher related webservices*/
    /**
     * Use To Create  Teacher Lecture.
     *
     * Request Object :
     * {"task":"teacher_create_lecture","taskData":{"courseid":"1","semesterid":"1","subjectid":"1", "teacherid":"1","secret_token":"86EBDD90"}}
     *
     */
    /*public function teacherCreateLecture($TaskData)
    {
        $input = array();
        $input['course'] = $TaskData->courseid;
        $input['semester'] = $TaskData->semesterid;
        $input['device_id'] = '0';
        $input['subject'] = $TaskData->subjectid;
        $input['class'] = '0';
        $input['starttime'] = date('Y-m-d H:i:s');
        $input['entry_via'] = 'webservice';
        $input['teacherid'] = $TaskData->teacherid;

        $Lectures = new Lectures();
        $Lectures->course = $input['course'];
        $Lectures->semester = $input['semester'];
        $Lectures->device_id = $input['device_id'];
        $Lectures->subject = $input['subject'];
        $Lectures->class = $input['class'];
        $Lectures->starttime = $input['starttime'];
        $Lectures->endtime = date('Y-m-d H:i:s');
        $Lectures->entry_via = $input['entry_via'];
        $Lectures->teacherid = $input['teacherid'];
        $Lectures->save();
        $lectid = $Lectures->id;

        if($lectid)
        {
            $students_data = Student::where('courseid',$input['course'])->where('semesterid',$input['semester'])->get();

            $this->JsonArray['message'] = '';
            $this->JsonArray['lecture_id'] = $lectid;
            $this->JsonArray['students_data'] = $students_data;
            $this->JsonArray['status_code'] = 200;
        }
        else
        {
            $this->JsonArray['message'] = 'Lecture not created! please try again';
            $this->JsonArray['status_code'] = 204;
        }
        header("content-type: application/json");
        echo json_encode($this->JsonArray);exit;
    }*/
    /**
     * @param $TaskData*
     *  {"task":"teacher_create_lecture","taskData":{"courseid":"1","semesterid":"1","secret_token":"86EBDD90"}}
     */
    public function teacherCreateLecture($TaskData)
    {
        $students_data = Student::where('courseid',$TaskData->courseid)->where('semesterid',$TaskData->semesterid)->get();

        if($students_data)
        {
            $this->JsonArray['message'] = '';
            $this->JsonArray['students_data'] = $students_data;
            $this->JsonArray['starttime'] = date('Y-m-d H:i:s');
            $this->JsonArray['status_code'] = 200;
        }
        else
        {
            $this->JsonArray['message'] = 'No students for this course';
            $this->JsonArray['status_code'] = 204;
        }
        header("content-type: application/json");
        echo json_encode($this->JsonArray);exit;
    }

    /***
     * @param $TaskData
     *   {"task":"teacher_save_lecture_attendance","taskData":{"teacherid":"1","courseid":"1","semesterid":"1","subjectid":"1","starttime":"2017-07-06 22:22:22","secret_token":"86EBDD90","student_data":{}}}
     */
    public function teacherSaveLectureAttendance($TaskData)
    {
        $input = array();
        $timestamp = date('Y-m-d H:i:s');
        $input['course'] = $TaskData->courseid;
        $input['semester'] = $TaskData->semesterid;
        $input['device_id'] = '0';
        $input['subject'] = $TaskData->subjectid;
        $input['class'] = '0';
        $input['starttime'] = $TaskData->starttime;
        $input['entry_via'] = 'webservice';
        $input['teacherid'] = $TaskData->teacherid;

        $Lectures = new Lectures();
        $Lectures->course = $input['course'];
        $Lectures->semester = $input['semester'];
        $Lectures->device_id = $input['device_id'];
        $Lectures->subject = $input['subject'];
        $Lectures->class = $input['class'];
        $Lectures->starttime = $input['starttime'];
        $Lectures->endtime = $timestamp;
        $Lectures->entry_via = $input['entry_via'];
        $Lectures->teacherid = $input['teacherid'];
        $Lectures->save();

        $lectureid = $Lectures->id;
        $teacherid = $TaskData->teacherid;

        $insert_arr = array();
        $student_data = $TaskData->student_data;
        $lectures_data = array();
        if($lectureid)
        {
            $query = Lectures::select('lectures.id','course.name as course_name','course.id as course_id','semester.name as semester_name','semester.id as semester_id','class.name as class_name','class.id as class_id','lectures.device_id as device_id','subject.name as subject_name','subject.id as subject_id','lectures.starttime','lectures.endtime','lectures.teacherid');
            $query->leftjoin('course','course.id','=','lectures.course');
            $query->leftjoin('semester','semester.id','=','lectures.semester');
            $query->leftjoin('class','class.id','=','lectures.class');
            $query->leftjoin('subject','subject.id','=','lectures.subject');
            $query->where('lectures.id',$lectureid);
            $query->where('lectures.teacherid',$teacherid);
            $lecture = $query->first();

            if(count($lecture) && count($student_data))
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

                foreach($student_data as $student)
                {
                    $major = $student->major;
                    $minor = $student->minor;
                    $present = ($student->present)?'y':'n';
                    $beacon_mac = $student->mac_address;
                    $rssi = '';
                    $factory_id = '';
                    $beacon_id = '';
                    $measured_power = '';
                    $beacon_type = '';
                    $rssi = '';

                    if($student->present)
                    {
                        $insert_arr[] = "('".$beacon_mac."','".$device_id."','".$minor."','".$major."','".$timestamp."','".$rssi."','".$factory_id."','".$beacon_id."','".$measured_power."','".$beacon_type."','".$lecture_id."','".$course_name."','".$semester_name."','".$class_name."','".$subject_name."','".$starttime."','".$endtime."','".$course_id."','".$semester_id."','".$class_id."','".$subject_id."','".$teacher_id."','".$present."')";
                    }
                }
            }
            if(count($insert_arr)>0)
            {
                $all_data = implode(',',$insert_arr);
                $insert_data = DB::connection('odbc')->statement("INSERT INTO TABLE ".$this->hadoop_db_conn.".iot_logs VALUES ".$all_data );

                $this->JsonArray['message'] = 'Attendance done successfully';
                $this->JsonArray['status_code'] = 200;
            }
            else
            {
                $this->JsonArray['message'] = 'No attendance data to register';
                $this->JsonArray['status_code'] = 204;
            }
        }
        header("content-type: application/json");
        echo json_encode($this->JsonArray);exit;
    }
    public function teacherPreviousSaveLectures($TaskData)
    {
        $teacherid = $TaskData->teacherid;
        $lectures_data = array();
        if($teacherid)
        {
            $timestamp = date('Y-m-d H:i:s');
            $query = Lectures::select('lectures.id','course.name as course_name','course.id as course_id','semester.name as semester_name','semester.id as semester_id','class.name as class_name','class.id as class_id','lectures.device_id as device_id','subject.name as subject_name','subject.id as subject_id','lectures.starttime','lectures.endtime');
            $query->leftjoin('course','course.id','=','lectures.course');
            $query->leftjoin('semester','semester.id','=','lectures.semester');
            $query->leftjoin('class','class.id','=','lectures.class');
            $query->leftjoin('subject','subject.id','=','lectures.subject');
            $query->where('lectures.endtime','<',$timestamp);
            $query->where('lectures.teacherid',$teacherid);
            $query->orderby('lectures.id','desc');
            $lectures = $query->get();

            foreach($lectures as $lecture)
            {
                $course_id = $lecture->course_id;
                $semester_id = $lecture->semester_id;
                $lectureid = $lecture->id;
                $present_data = DB::connection('odbc')->select( DB::raw("SELECT * FROM ".$this->hadoop_db_conn.".iot_logs  where teacher_id='".$teacherid."' and lecture_id='".$lectureid."' ") );
                $lecture->present_count = count($present_data);
                $lecture->total_count = Student::select('students.id','students.name as student_name','students.minor_id','students.major_id','students.avatar')->where('courseid',$course_id)->where('semesterid',$semester_id)->count();
                $lecture->percent = round(($lecture->present_count*100)/$lecture->total_count,2);
                $lectures_data[] = $lecture;
            }

            if(count($lectures_data)>0)
            {
                $this->JsonArray['message'] = '';
                $this->JsonArray['teacher_id'] = $teacherid;
                $this->JsonArray['lectures_data'] = $lectures_data;
                $this->JsonArray['status_code'] = 200;
            }
            else
            {
                $this->JsonArray['teacher_id'] = $teacherid;
                $this->JsonArray['lectures_data'] = '';
                $this->JsonArray['status_code'] = 204;
                $this->JsonArray['message'] = 'No lectures found';
                $this->JsonArray['status_code'] = 204;
            }
        }
        header("content-type: application/json");
        echo json_encode($this->JsonArray);exit;
    }
    public function teacherPreviousLectureAttendance($TaskData)
    {
        $get_data = DB::connection('odbc')->select( DB::raw("SELECT * FROM ".$this->hadoop_db_conn.".iot_logs  where teacher_id='".$TaskData->teacherid."' and lecture_id='".$TaskData->lectureid."' ") );

        $log_data = array();
        $final_log = array();
        if(count($get_data))
        {
            $teacher_log_data = DB::connection('odbc')->select( DB::raw("SELECT concat(minor_id,'|',major_id) as mm_id FROM ".$this->hadoop_db_conn.".iot_logs where teacher_id='".$TaskData->teacherid."' and lecture_id='".$TaskData->lectureid."' and teacher_app ='y' "));

            $teacher_log_count = count($teacher_log_data);
            if($teacher_log_count)
            {
                for($i=0;$i<$teacher_log_count;$i++)
                {
                    $log_data[] = $teacher_log_data[$i]->mm_id;
                }

                $query = Student::select('students.id','students.name as student_name','students.minor_id','students.major_id','students.avatar');
                $query->where('courseid',$TaskData->courseid);
                $query->where('semesterid',$TaskData->semesterid);
                $students = $query->get();

                foreach($students as $student)
                {
                    $check_val = $student->minor_id.'|'.$student->major_id;
                    if(in_array($check_val,$log_data))
                    {
                        $student->present = '1';
                    }
                    else
                    {
                        $student->present = '0';
                    }
                    $student->avatar = ($student->avatar != '')?asset($student->avatar):'';
                    $final_log[] = $student;
                }
            }

            if (!count($final_log))
            {
                $this->JsonArray['total'] = count($final_log);
                $this->JsonArray['message'] = "No Data available.";
                $this->JsonArray['attendance']    = $final_log;
                $this->JsonArray['status_code'] = 204;
            }
            else
            {
                $this->JsonArray['total'] = count($final_log);
                $this->JsonArray['message'] = "";
                $this->JsonArray['attendance']    = $final_log;
                $this->JsonArray['status_code'] = 200;
            }
        }
        else
        {
            $this->JsonArray['total'] = count($final_log);
            $this->JsonArray['message'] = "";
            $this->JsonArray['attendance'] = $final_log;
            $this->JsonArray['status_code'] = 200;
        }
        header("content-type: application/json");
        echo json_encode($this->JsonArray);exit;
    }
    public function getTestLog()
    {
        $get_data = DB::connection('odbc')->select( DB::raw("SELECT * FROM test_logs.test_iot_logs order by logged_timestamp desc ") );
        $JsonArray['logs'] = $get_data;
        dd($get_data);
        header("content-type: application/json");
        echo json_encode($JsonArray);exit;
    }
}