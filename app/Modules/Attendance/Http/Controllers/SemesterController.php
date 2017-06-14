<?php

namespace App\Modules\Attendance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Models\Course;
use Illuminate\Http\Request;

use App\Modules\Attendance\Http\Requests;
use App\Modules\Attendance\Models\Semester;
use Yajra\Datatables\Facades\Datatables;

class SemesterController extends Controller
{
    public function index(Request $request)
    {
        return view('attendance::semester.index');
    }

    public function SemesterData(Request $request)
    {
        $provinces = Semester::leftjoin('course','course.id','=','semester.courseid')->select('semester.*','course.name as course_name');
        return Datatables::of($provinces)->make(true);
    }

    public function create()
    {
        $courses = $this->courseList();
        return view('attendance::semester.create',compact('courses'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);
        $input = $request->all();

        $Semester = new Semester();
        $Semester->name        =  $request->get('name');
        $Semester->courseid        =  $request->get('courseid');
        $Semester->description =  $request->get('description');
        $Semester->save();

        return redirect()->route('semester.index')->with('success','Semester created successfully');
    }

    public function edit($id)
    {
        $data     = Semester::find($id);
        $courses = $this->courseList();
        return view('attendance::semester.edit',compact('data','courses'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);
        $input = $request->all();
        $semester = Semester::find($id);
        $semester->update($input);
        return redirect()->route('semester.index')->with('success','Semester updated successfully');
    }
    public function destroy(Request $request)
    {
        $Input   = $request->all();
        $IDs     = $Input['IDs'];
        $IDArray = explode(',', $IDs);

        foreach ($IDArray as $key => $Id)
        {
            Semester::find($Id)->delete();
        }
        return redirect()->route('semester.index')->with('flash_message','Semester deleted successfully');
    }
    public function courseList()
    {
        return Course::pluck('name','id')->toArray();
    }
}
