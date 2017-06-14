<?php

namespace App\Modules\Proximity\Http\Controllers;

use Illuminate\Http\Request;
use App\Modules\Proximity\Http\Requests;
use App\Modules\Proximity\Models\AdsManager;
use App\Modules\Proximity\Models\ScheduleManager;
use App\Modules\Beaconmanager\Models\Beacon;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;


class ScheduleManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = ScheduleManager::orderBy('id','DESC')->paginate();

        foreach ($items as $key => $value)
        {
            if($value['ads_manager_id'])
            {
                $ads_manager               = AdsManager::select('title')->where('id', $value['ads_manager_id'])->get();
                $ads_manager_name          = json_decode($ads_manager);
                $value['ads_manager_name'] = $ads_manager_name[0]->title;
            }
        }

        $ads_managers = AdsManager::get();

        return view('proximity::scheduleManager.index',compact('items', 'ads_managers'));
    }

    public function ScheduleManagerData(Request $request)
    {
        $query = ScheduleManager::select('schedule_managers.id','schedule_managers.ads_manager_id','schedule_managers.title','schedule_managers.beacon_id','schedule_managers.date','schedule_managers.start_time','schedule_managers.end_time','schedule_managers.created_at','schedule_managers.updated_at','schedule_managers.status','ads_managers.title as ads_manager_name');
        $provinces = $query->leftjoin('ads_managers','ads_managers.id','=','schedule_managers.ads_manager_id');

        //$provinces = AdsManager::select('*');
        return Datatables::of($provinces)->make(true);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $adsManagers = AdsManager::pluck('title','id')->toArray();
        $beacons = Beacon::pluck('name','id')->toArray();

        return view('proximity::scheduleManager.create',compact('adsManagers', 'beacons'));
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
            'title'          => 'required',
            'ads_manager_id' => 'required',
            'beacon_id'      => 'required'
        ]);

        $input = $request->all();
        $ScheduleManager                 = new ScheduleManager();
        $ScheduleManager->title          =  $input['title'];
        $ScheduleManager->ads_manager_id =  $input['ads_manager_id'];
        $ScheduleManager->beacon_id      =  $input['beacon_id'];
        $ScheduleManager->date           =  $input['date'];
        $ScheduleManager->start_time     =  $input['start_time'];
        $ScheduleManager->end_time       =  $input['end_time'];
        $ScheduleManager->status         =  $input['status'];
        $ScheduleManager->save();

        //$scheduleManager = ScheduleManager::create($input);

        /*// Store in audit
        $auditData                = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'scheduleManager';
        $auditData['action']      = 'scheduleManager.create';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);*/

        return redirect()->route('scheduleManager.index')->with('success','Schedule Manager created successfully');
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
        $item        = ScheduleManager::find($id);
        $adsManagers = AdsManager::pluck('title','id')->toArray();
        $beacons     = Beacon::pluck('name','id')->toArray();

        return view('proximity::scheduleManager.edit',compact('item','adsManagers', 'beacons'));
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
            'title'          => 'required',
            'ads_manager_id' => 'required',
            'beacon_id'      => 'required'
        ]);

        $input = $request->all();
        ScheduleManager::find($id)->update($input);

        return redirect()->route('scheduleManager.index')->with('flash_message','Schedule Manager updated successfully');
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
        $scheduleManagerIDs     = $Input['scheduleManagerIDs'];
        $scheduleManagerIDArray = explode(',', $scheduleManagerIDs);

        foreach ($scheduleManagerIDArray as $key => $scheduleManagerID)
        {
            ScheduleManager::find($scheduleManagerID)->delete();
        }

        return redirect()->route('scheduleManager.index')->with('flash_message','Schedule Manager deleted successfully');
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
        $ScheduleManager = ScheduleManager::find($id);

        if ($statusMode == 0){
            $ScheduleManager->status = 0;
        }else{
            $ScheduleManager->status = 1;
        }
        $ScheduleManager->save();

        return redirect()->route('scheduleManager.index')->with('flash_message','Status changed successfully');
    }
}
