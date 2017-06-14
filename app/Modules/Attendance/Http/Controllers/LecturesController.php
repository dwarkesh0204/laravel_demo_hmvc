<?php

namespace App\Modules\Attendance\Http\Controllers;

use App\Modules\Attendance\Models\Notifications;
use App\Modules\Attendance\Models\Lectures;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\Datatables\Facades\Datatables;
use App\Modules\Attendance\Models\Course;
use App\Modules\Attendance\Models\Semester;
use App\Modules\Attendance\Models\Subject;
use App\Modules\Attendance\Models\Classes;
use App\Modules\Attendance\Http\Requests;
use App\Http\Controllers\Controller;

class LecturesController extends Controller
{
    public function index(Request $request)
    {
        $courses = Course::pluck('name','id');
        return view('attendance::lectures.index',compact('courses'));
    }

    public function LecturesData(Request $request)
    {
        $query = Lectures::select('lectures.id','course.name as course','subject.name as subject','semester.name as semester','class.name as class','lectures.starttime','lectures.endtime');
        $query->leftjoin('course','course.id','=','lectures.course');
        $query->leftjoin('subject','subject.id','=','lectures.subject');
        $query->leftjoin('semester','semester.id','=','lectures.semester');
        $provinces = $query->leftjoin('class','class.id','=','lectures.class');
        return Datatables::of($provinces)->make(true);
    }

    public function create()
    {
        $course   = Course::pluck('name','id')->toArray();
        $semester = Semester::pluck('name','id')->toArray();
        $class    = Classes::pluck('name','id')->toArray();
        $subject  = Subject::pluck('name','id')->toArray();

        return view('attendance::lectures.create',compact('course','semester','class','subject'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'course'    => 'required',
            'semester'  => 'required',
            'subject'   => 'required',
            'startdate' => 'required',
            'starttime' => 'required',
            'enddate'   => 'required',
            'endtime'   => 'required',
        ]);

        $input = $request->all();
        $input['device_id'] = '';
        if($input['startdate'] != '' && $input['starttime']  != '' && $input['enddate'] != '' && $input['endtime'] != '')
        {
            $input['starttime'] = $input['startdate'] . ' ' . $input['starttime'] . ':00';
            $input['endtime']   = $input['enddate'] . ' ' . $input['endtime'] . ':00';
            if($input['class'])
            {
                $device = Classes::where('id',$input['class'])->select('iot_id')->first();
                $input['device_id'] = $device->iot_id;
            }
            if (Carbon::parse($input['starttime'])->timestamp > Carbon::parse($input['endtime'])->timestamp)
            {
                return redirect()->back()->with('error','Start Date is greater then End Date');
            }

            $query = Lectures::where('class',$input['class']);
            $query->where( function ( $query ) use ($input){
                $query->where( function ( $query ) use ($input) {
                    $query->where('starttime','<=', $input['starttime'])
                        ->where('endtime','>=',$input['starttime']);
                });
                $query->orWhere( function ( $query ) use ($input){
                    $query->where('starttime','<=', $input['endtime'])
                        ->where('endtime','>=',$input['endtime']);
                });
            });
            $check_lec = $query->select('device_id')->count();
            if($check_lec)
            {
                return redirect()->back()->with('error','Lecture already exists in the system');
            }
        }
        $Lectures = new Lectures();
        $Lectures->course = $input['course'];
        $Lectures->semester = $input['semester'];
        $Lectures->subject = $input['subject'];
        $Lectures->class = $input['class'];
        $Lectures->starttime = $input['starttime'];
        $Lectures->endtime = $input['endtime'];
        $Lectures->device_id = $input['device_id'];
        $Lectures->entry_via = 'admin panel';
        $Lectures->teacherid = 0;
        $Lectures->save();
        return redirect()->route('lectures.index')->with('success','Lectures created successfully');
    }
    public function getSubjects(Request $request)
    {
        $course = Course::find($request->input('courseid'));
        if(count($course))
        {
            $subs = Subject::whereIn('id',explode(',',$course->subject_ids))->get(['name','id']);
        }
        else{
            $subs = '';
        }
        if($request->ajax()){
            return response()->json([
                'subs' => $subs
            ]);
        }
    }
    public function destroy(Request $request)
    {
        $Input   = $request->all();
        $IDs     = $Input['IDs'];
        $IDArray = explode(',', $IDs);
        $hadoop_con = \Session::get('mysql_attendance_database_conn');
        foreach ($IDArray as $key => $Id)
        {
            Lectures::find($Id)->delete();
            \DB::connection('odbc')->statement("Delete from ".$hadoop_con.".iot_logs WHERE lecture_id = $Id");
        }
        return redirect()->route('lectures.index')->with('flash_message','Lectures deleted successfully');
    }
}