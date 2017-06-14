<?php

namespace App\Modules\Attendance\Http\Controllers;

use App\Modules\Attendance\Models\Teacher;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Yajra\Datatables\Facades\Datatables;

class TeachersController extends Controller
{
    public function index(Request $request)
    {
        return view('attendance::teachers.index');
    }

    public function TeachersData(Request $request)
    {
        $provinces = Teacher::select('*');

        return Datatables::of($provinces)->make(true);
    }

    public function create()
    {
        return view('attendance::teachers.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name'         => 'required',
            'password' => 'required|same:confirm-password',
            'phone_number' => 'required'
        ]);

        if (!empty($request->email))
        {
            if(Teacher::where('email',$request->email)->count())
            {
                return redirect()->back()->with('error','Email already exists');
            }
        }

        $input = $request->all();
        $teacher = new Teacher();
        $teacher->name          =  $request->get('name');
        $teacher->email         =  $request->get('email');
        $teacher->phone_number  =  $request->get('phone_number');
        $teacher->password      =  \Hash::make($request->get('password'));
        $teacher->gender        =  $request->get('gender');
        $teacher->qualification =  $request->get('qualification');
        $teacher->description   =  $request->get('description');
        $teacher->status        =  $request->get('status');
        $teacher->save();

        $teacherId = $teacher->getAttribute('id');

        if($request->hasFile('avatar')) {
            $file = $request->file('avatar');

            // Create Directory
            \Storage::disk('upload')->makeDirectory('Teachers/'.$teacherId);

            $storage = public_path()."/uploads/Teachers/".$teacherId;

            // Create A FileName From Uploaded File.
            $fileName = time().$request->file('avatar')->getClientOriginalName();

            // Remove Spaces In The Filename And Convert To LowerCase.
            $fileName = str_replace(' ', '', strtolower($fileName));

            // Move The File To Storage Directory
            if($request->file('avatar')->move($storage,$fileName)){
                $FileNameStore = "/uploads/Teachers/".$teacherId."/".$fileName;
                $teacher       = Teacher::find($teacherId);
                $teacher->avatar = $FileNameStore;
                $teacher->save();
            }
        }

        return redirect()->route('teachers.index')->with('success','Teacher created successfully');
    }

    public function edit($id)
    {
        $data  = Teacher::find($id);
        return view('attendance::teachers.edit',compact('data'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name'         => 'required',
            'password'     => 'required|same:confirm-password',
            'phone_number' => 'required'
        ]);

        if (!empty($request->email))
        {
            if(Teacher::where('email',$request->email)->where('id','!=',$id)->count())
            {
                return redirect()->back()->with('error','Email already exists');
            }
        }

        $input = $request->all();
        $input['password']  =  \Hash::make($request->password);
        $teacher = Teacher::find($id);
        $teacher->update($input);

        if($request->hasFile('avatar')) {
            $file = $request->file('avatar');

            // Create Directory
            \Storage::disk('upload')->makeDirectory('Teachers/'.$id);

            $storage = public_path()."/uploads/Teachers/".$id;

            // Create A FileName From Uploaded File.
            $fileName = time().$request->file('avatar')->getClientOriginalName();

            // Remove Spaces In The Filename And Convert To LowerCase.
            $fileName = str_replace(' ', '', strtolower($fileName));

            // Move The File To Storage Directory
            if($request->file('avatar')->move($storage,$fileName)){

                $FileNameStore   = "/uploads/Teachers/".$id."/".$fileName;
                $teacher         = Teacher::find($id);
                $teacher->avatar = $FileNameStore;
                $teacher->save();
            }
        }

        return redirect()->route('teachers.index')->with('success','Teacher updated successfully');
    }
    public function destroy(Request $request)
    {
        $Input      = $request->all();
        $IDs     = $Input['IDs'];
        $IDArray = explode(',', $IDs);

        foreach ($IDArray as $key => $Id)
        {
            Teacher::find($Id)->delete();
        }
        return redirect()->route('teachers.index')->with('flash_message','Teacher deleted successfully');
    }

    /**
     * Delete Image.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteImage(Request $request)
    {
        $Input     = $request->all();
        $teacherId = $Input['id'];
        $teacher   = Teacher::find($teacherId);

        $imagePath = $teacher['avatar'];
        unlink(public_path().$imagePath);
        $teacher['avatar'] = "";
        $teacher->save();

        return redirect()->back()->with('flash_message','Image deleted successfully');
    }
}
