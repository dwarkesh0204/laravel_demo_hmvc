<?php

namespace App\Modules\Attendance\Http\Controllers;

use Illuminate\Http\Request;
use App\Modules\Attendance\Http\Requests;

use App\Modules\Attendance\Models\Classes;
use Carbon\Carbon;
use Yajra\Datatables\Facades\Datatables;
use App\Modules\Attendance\Models\Iot;
use App\Http\Controllers\Controller;
use App\Models\Project_Devices;


class ClassController extends Controller
{
    public function index(Request $request)
    {
        return view('attendance::class.index');
    }

    public function ClassData(Request $request)
    {
        $provinces = Classes::select('*');
        return Datatables::of($provinces)->make(true);
    }

    public function create()
    {
        $lists = array();
        $query = Iot::on(\Session::get('mysql_bm_database_conn'));
        $assigned_pdIds = Project_Devices::select('device_id')->get();
        if(count($assigned_pdIds))
        {
            $query->WhereNotIn('device_id',$assigned_pdIds);
        }
        $lists = $query->pluck('device_id','id')->toArray();
        return view('attendance::class.create',compact('lists'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name'      => 'required',
            /*'device_id' => 'required|unique:class',*/
        ]);
        $date = Carbon::now();
        $input = $request->all();
        $iot = Iot::on(\Session::get('mysql_bm_database_conn'))->find($input['device_id']);
        $Project_Devices = new Project_Devices();
        $Project_Devices->device_id = $iot->device_id;
        $Project_Devices->project_id = \Session::get('attendance_conn_id');
        $Project_Devices->mac_address =  '';
        $Project_Devices->save();

        $Classes = new Classes();
        $Classes->name      =  $request->name;
        $Classes->iot_id    =  $iot->device_id;
        $Classes->device_id =  $iot->id;
        $Classes->save();

        return redirect()->route('class.index')->with('success','Class created successfully');
    }

    public function edit($id)
    {
        $data     = Classes::find($id);
        $lists = Iot::on(\Session::get('mysql_bm_database_conn'))->pluck('device_id','id')->toArray();
        return view('attendance::class.edit',compact('data','lists'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name'      => 'required',
            /*'device_id' => 'required|unique:class,device_id,'.$id,*/
        ]);

        $input = $request->all();

        $class = Classes::find($id);
        $input = $request->all();
        $iot   = Iot::find($input['device_id']);
        $input['iot_id']    = $iot->device_id;
        $input['device_id'] = $iot->id;
        $class->update($input);

        return redirect()->route('class.index')->with('success','Class updated successfully');
    }

    public function destroy(Request $request)
    {
        $Input   = $request->all();
        $IDs     = $Input['IDs'];
        $IDArray = explode(',', $IDs);

        foreach ($IDArray as $key => $Id)
        {
            Classes::find($Id)->delete();
        }
        return redirect()->route('class.index')->with('flash_message','Class deleted successfully');
    }
}