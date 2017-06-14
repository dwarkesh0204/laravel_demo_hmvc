<?php

namespace App\Modules\Attendance\Http\Controllers;

use App\Modules\Attendance\Models\Semester;
use App\Modules\Attendance\Models\Subject;
use App\Http\Controllers\Controller;
use App\Modules\Attendance\Models\Course;
use Illuminate\Http\Request;
use Yajra\Datatables\Facades\Datatables;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $courses = Course::pluck('name','id');
        return view('attendance::subject.index',compact('courses'));
    }

    public function SubjectData(Request $request)
    {
        $provinces = Subject::leftjoin('course','course.id','=','subject.courseid')->leftjoin('semester','semester.id','=','subject.semesterid')->select('subject.*','course.name as course_name','semester.name as semester_name');
        return Datatables::of($provinces)->make(true);
    }

    public function create()
    {
        $courses = $this->courseList();
        return view('attendance::subject.create',compact('courses'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $input = $request->all();
        $Subject = new Subject();
        $Subject->name =  $request->get('name');
        $Subject->courseid =  $request->get('courseid');
        $Subject->semesterid =  $request->get('semesterid');
        $Subject->description =  $request->get('description');
        $Subject->save();

        return redirect()->route('subject.index')->with('success','Subject created successfully');
    }

    public function edit($id)
    {
        $data     = Subject::find($id);
        $courses = $this->courseList();
        return view('attendance::subject.edit',compact('data','courses'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);
        $input = $request->all();
        $subject = Subject::find($id);
        $subject->update($input);
        return redirect()->route('subject.index')->with('success','Subject updated successfully');
    }
    public function destroy(Request $request)
    {
        $Input      = $request->all();
        $IDs     = $Input['IDs'];
        $IDArray = explode(',', $IDs);

        foreach ($IDArray as $key => $Id)
        {
            Subject::find($Id)->delete();
        }
        return redirect()->route('subject.index')->with('flash_message','Subject deleted successfully');
    }
    public function courseList()
    {
        return Course::pluck('name','id')->toArray();
    }
    public function getSemesterData(Request $request)
    {
        return Semester::where('courseid',$request->courseid)->pluck('name','id')->toArray();
    }
    public function getSubjectsData(Request $request)
    {
        if($request->type == 'semesters')
        {
            $query = Semester::select('semester.name as semester_name','semester.id as semester_id');
            $query->where('semester.courseid',$request->courseid);
        }
        else
        {
            $query = Subject::select('subject.id as subject_id','subject.name as subject_name');
            $query->where('subject.courseid',$request->courseid);
            $query->where('subject.semesterid',$request->semesterid);
        }
        $data = $query->get();

        return $data;
    }
}
