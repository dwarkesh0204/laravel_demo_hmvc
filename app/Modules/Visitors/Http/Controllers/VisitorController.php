<?php

namespace App\Modules\Visitors\Http\Controllers;

use App\Modules\Visitors\Models\Visitor;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Yajra\Datatables\Facades\Datatables;

use App\Modules\Beaconmanager\Models\Beacon;

class VisitorController extends Controller
{
    public function index(Request $request)
    {
        return view('visitors::visitors.index');
    }

    public function VisitorData(Request $request)
    {
        $provinces = Visitor::select('*');

        return Datatables::of($provinces)->make(true);
    }

    public function create()
    {
        $lists = array();
        $lists = Beacon::pluck('name','id')->toArray();

        return view('visitors::visitors.create',compact('lists'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $input = $request->all();
        $data = Beacon::find($request->get('card_no'));

        $Visitor = new Visitor();
        $Visitor->card_no  =  $request->get('card_no');
        $Visitor->name     =  $request->get('name');
        $Visitor->email    =  $request->get('email');
        $Visitor->purpose  =  $request->get('purpose');
        $Visitor->phone_no =  $request->get('phone_no');
        $Visitor->mac_add  =  $data->macid;
        $Visitor->save();

        return redirect()->route('visitors.index')->with('success','Visitor created successfully');
    }

    public function edit($id)
    {
        $data  = Visitor::find($id);
        $lists = array();
        $lists = Beacon::pluck('name','id')->toArray();

        return view('visitors::visitors.edit',compact('data', 'lists'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $input = $request->all();
        $visitors = Visitor::find($id);
        $data     = Beacon::find($request->get('card_no'));
        $input['mac_add']  =  $data->macid;
        $visitors->update($input);

        return redirect()->route('visitors.index')->with('success','Visitor updated successfully');
    }
    public function destroy(Request $request)
    {
        $Input   = $request->all();
        $IDs     = $Input['IDs'];
        $IDArray = explode(',', $IDs);

        foreach ($IDArray as $key => $Id)
        {
            Visitor::find($Id)->delete();
        }

        return redirect()->route('visitors.index')->with('flash_message','Visitor deleted successfully');
    }

    /**
     * publish the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus(Request $request, $id)
    {
        $Input      = $request->all();
        $statusMode = $Input['mode'];
        $Visitor = Visitor::find($id);

        if ($statusMode == 0){
            $Visitor->status = 0;
        }else{
            $Visitor->status = 1;
        }
        $Visitor->save();

        return redirect()->route('visitors.index')->with('flash_message','Status changed successfully');
    }
}
