<?php

namespace App\Modules\Attendance\Http\Controllers;

use App\Modules\Attendance\Models\Student;
use App\Modules\Attendance\Models\Course;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Yajra\Datatables\Facades\Datatables;

class StudentsController extends Controller
{
    public function index(Request $request)
    {
        return view('attendance::students.index');
    }

    public function StudentsData(Request $request)
    {
        $provinces = Student::leftjoin('course','course.id','=','students.courseid')->leftjoin('semester','semester.id','=','students.semesterid')->select('students.*','course.name as course_name','semester.name as semester_name');
        return Datatables::of($provinces)->make(true);
    }

    public function create()
    {
        $courses = Course::pluck('name','id')->toArray();
        return view('attendance::students.create',compact('courses'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $input = $request->all();
        $Student = new Student();
        $Student->name =  $request->get('name');
        $Student->email =  $request->get('email');
        $Student->courseid =  $request->get('courseid');
        $Student->semesterid =  $request->get('semesterid');
        $Student->phone_number =  $request->get('phone_number');
        $Student->roll_no =  $request->get('roll_no');
        $Student->device_id =  '';
        $Student->device_type =  '';
        $Student->device_token =  '';
        $Student->mac_address =  '';

        $res = $this->checkMinorMajor(rand(1, 9999),rand(1, 9999));
        $Student->minor_id = $res['minor'];
        $Student->major_id = $res['major'];
        $Student->save();

        $Studentid = $Student->getAttribute('id');

        if($request->hasFile('avatar')) {
            $file = $request->file('avatar');

            // Create Directory
            \Storage::disk('upload')->makeDirectory('students/'.$Studentid);

            $storage = public_path()."/uploads/students/".$Studentid;

            // Create A FileName From Uploaded File.
            $fileName = time().$request->file('avatar')->getClientOriginalName();

            // Remove Spaces In The Filename And Convert To LowerCase.
            $fileName = str_replace(' ', '', strtolower($fileName));

            // Move The File To Storage Directory
            if($request->file('avatar')->move($storage,$fileName)){
                $FileNameStore = "/uploads/students/".$Studentid."/".$fileName;
                $student = Student::find($Studentid);
                $student->avatar = $FileNameStore;
                $student->save();
            }
        }

        return redirect()->route('students.index')->with('success','Student created successfully');
    }

    public function edit($id)
    {
        $data     = Student::find($id);
        $courses = Course::pluck('name','id')->toArray();
        return view('attendance::students.edit',compact('data','courses'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);
        $input = $request->all();
        $student = Student::find($id);
        if($student->minor_id == '' && $student->major_id  == '')
        {
            $res = $this->checkMinorMajor(rand(1, 9999),rand(1, 9999));
            $student->minor_id = $res['minor'];
            $student->major_id = $res['major'];
        }
        $student->update($input);

        if($request->hasFile('avatar')) {
            $file = $request->file('avatar');

            // Create Directory
            \Storage::disk('upload')->makeDirectory('students/'.$id);

            $storage = public_path()."/uploads/students/".$id;

            // Create A FileName From Uploaded File.
            $fileName = time().$request->file('avatar')->getClientOriginalName();

            // Remove Spaces In The Filename And Convert To LowerCase.
            $fileName = str_replace(' ', '', strtolower($fileName));

            // Move The File To Storage Directory
            if($request->file('avatar')->move($storage,$fileName)){

                $FileNameStore   = "/uploads/students/".$id."/".$fileName;
                $teacher         = Student::find($id);
                $teacher->avatar = $FileNameStore;
                $teacher->save();
            }
        }

        return redirect()->route('students.index')->with('success','Student updated successfully');
    }
    public function destroy(Request $request)
    {
        $Input      = $request->all();
        $IDs     = $Input['IDs'];
        $IDArray = explode(',', $IDs);

        foreach ($IDArray as $key => $Id)
        {
            Student::find($Id)->delete();
        }
        return redirect()->route('students.index')->with('flash_message','Student deleted successfully');
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
}
