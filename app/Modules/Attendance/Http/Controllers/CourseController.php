<?php
namespace App\Modules\Attendance\Http\Controllers;

use Illuminate\Http\Request;

use App\Modules\Attendance\Http\Requests;
use App\Modules\Attendance\Models\Course;
use App\Modules\Attendance\Models\Subject;
use Yajra\Datatables\Facades\Datatables;
use App\Http\Controllers\Controller;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        return view('attendance::course.index');
    }

    public function CourseData(Request $request)
    {
        $provinces = Course::select('*');
        return Datatables::of($provinces)->make(true);
    }

    public function create()
    {
        $lists = Subject::pluck('name','id')->toArray();
        return view('attendance::course.create',compact('lists'));
    }

    public function store(Request $request)
    {
        if(empty($request->name))
        {
            return redirect()->back()->with('error','Course name is required');
        }
        if (!empty($request->name))
        {
            if(Course::where('name',$request->name)->count())
            {
                return redirect()->back()->with('error','Course name already exists');
            }
        }
        $input = $request->all();
        $Course = new Course();
        $Course->name        =  $input['name'];
        $Course->description =  $input['description'];
        /*$Course->subject_ids =  implode(',',$input['subject_ids']);*/
        $Course->save();

        return redirect()->route('course.index')->with('success','Course created successfully');
    }

    public function edit($id)
    {
        $data  = Course::find($id);
        return view('attendance::course.edit',compact('data'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);
        if (!empty($request->name))
        {
            if(Course::where('name',$request->name)->where('id','!=',$id)->count())
            {
                return redirect()->back()->with('error','Course name already exists');
            }
        }
        $input = $request->all();
        $course = Course::find($id);
        $course->update($input);

        return redirect()->route('course.index')->with('success','Course updated successfully');
    }

    public function destroy(Request $request)
    {
        $Input   = $request->all();
        $IDs     = $Input['IDs'];
        $IDArray = explode(',', $IDs);

        foreach ($IDArray as $key => $Id)
        {
            Course::find($Id)->delete();
        }

        return redirect()->route('course.index')->with('flash_message','Course deleted successfully');
    }
}
