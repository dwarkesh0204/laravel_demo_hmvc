<?php

namespace App\Modules\Attendance\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Attendance\Models\Webservice;

class WebserviceController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){
        $this->WebServiceModel = new WebService();
    }

    /**
     * Use to get Request Object
     */
    public function index(Request $request){

        $Data     = json_decode(stripslashes(htmlspecialchars_decode(urldecode($request->reqObject))));
        $Task     = $Data->task;
        $TaskData = $Data->taskData;

        if($Task != ""){
            switch ($Task) {
                case 'register':
                    return $this->WebServiceModel->register($TaskData);
                    break;
                case 'attendance':
                    return $this->WebServiceModel->attendance($TaskData);
                    break;
                case 'notifications':
                    return $this->WebServiceModel->notifications($TaskData);
                    break;
                case 'courses_params':
                    return $this->WebServiceModel->coursesParams($TaskData);
                    break;
                case 'semesters_params':
                    return $this->WebServiceModel->semestersParams($TaskData);
                    break;
                case 'subject_params':
                    return $this->WebServiceModel->subjectParams($TaskData);
                    break;
                case 'teacher_login':
                    return $this->WebServiceModel->teacherLogin($TaskData);
                    break;
                case 'teacher_create_lecture':
                    return $this->WebServiceModel->teacherCreateLecture($TaskData);
                    break;
                case 'teacher_save_lecture_attendance':
                    return $this->WebServiceModel->teacherSaveLectureAttendance($TaskData);
                    break;
                case 'teacher_previous_save_lecture':
                    return $this->WebServiceModel->teacherPreviousSaveLectures($TaskData);
                    break;
                case 'teacher_previous_lecture_attendance':
                    return $this->WebServiceModel->teacherPreviousLectureAttendance($TaskData);
                    break;
                case 'test_logs':
                    return $this->WebServiceModel->getTestLog();
                    break;



            }
        }else{
            echo "No task Found";
        }
    }
}