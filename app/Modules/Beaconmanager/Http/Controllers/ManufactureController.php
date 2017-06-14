<?php

namespace App\Modules\Beaconmanager\Http\Controllers;

use App\Modules\Beaconmanager\Models\Manufacture;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;

class ManufactureController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('beaconmanager::manufacture.index');
    }

    public function ManufactureData(Request $request)
    {
        $provinces = Manufacture::select('*');
        return Datatables::of($provinces)->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user_id = \Auth::user()->id;
        return view('beaconmanager::manufacture.create',compact('user_id'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'mac_series'=>'required'
        ]);

        $manufacture = new Manufacture();
        $manufacture->title = $request->title;
        $manufacture->description = $request->description;
        $manufacture->mac_series = $request->mac_series;
        $manufacture->status = $request->status;
        $manufacture->save();

        return redirect()->route('manufacture.index')->with('flash_message','Manufacturer created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item = Manufacture::find($id);
        $user_id = \Auth::user()->id;
        return view('beaconmanager::manufacture.edit',compact('item','user_id'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required'
        ]);
        $input = $request->all();
        Manufacture::find($id)->update($input);

        return redirect()->route('manufacture.index')->with('flash_message','Manufacturer updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $Input      = $request->all();
        $catIDs     = $Input['catIDs'];
        $catIDArray = explode(',', $catIDs);

        foreach ($catIDArray as $key => $catId)
        {
            Manufacture::find($catId)->delete();
        }

        return redirect()->route('manufacture.index')->with('flash_message','Manufacturer deleted successfully');
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
        $Manufacture    = Manufacture::find($id);

        if ($statusMode == 0){
            $Manufacture->status = 0;
        }else{
            $Manufacture->status = 1;
        }
        $Manufacture->save();

        return redirect()->route('manufacture.index')->with('flash_message','Status changed successfully');
    }
}