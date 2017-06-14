<?php

namespace App\Modules\Proximity\Http\Controllers;


use Illuminate\Http\Request;
use App\Modules\Proximity\Http\Requests;
use App\Modules\Proximity\Models\CampaignManager;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;

class CampaignManagerController extends Controller
{
    /**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		$items = CampaignManager::orderBy('id','DESC')->paginate();
		return view('proximity::campaignManager.index',compact('items'));
	}

    public function CampaignManagerData(Request $request)
    {
        $provinces = CampaignManager::select('*');
        return Datatables::of($provinces)->make(true);
    }

	/**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('proximity::campaignManager.create');
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
            'title' => 'required'
        ]);

        $input                        = $request->all();
        $CampaignManager              = new CampaignManager();
        $CampaignManager->title       =  $input['title'];
        $CampaignManager->description =  $input['description'];
        $CampaignManager->status      =  $input['status'];
        $CampaignManager->save();

        //$campaignManager = CampaignManager::create($input);

        /*// Store in audit
        $auditData                = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'campaignManager';
        $auditData['action']      = 'campaignManager.create';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);*/

        return redirect()->route('campaignManager.index')->with('success','Campaign Manager created successfully');
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
        $item = CampaignManager::find($id);
        return view('proximity::campaignManager.edit',compact('item'));
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
        CampaignManager::find($id)->update($input);

        return redirect()->route('campaignManager.index')->with('flash_message','Campaign Manager updated successfully');
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
        $campaignManagerIDs     = $Input['campaignManagerIDs'];
        $campaignManagerIDArray = explode(',', $campaignManagerIDs);

        foreach ($campaignManagerIDArray as $key => $campaignManagerID)
        {
            CampaignManager::find($campaignManagerID)->delete();
        }

        return redirect()->route('campaignManager.index')->with('flash_message','Campaign Manager deleted successfully');
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
        $CampaignManager = CampaignManager::find($id);

        if ($statusMode == 0){
            $CampaignManager->status = 0;
        }else{
            $CampaignManager->status = 1;
        }
        $CampaignManager->save();

        return redirect()->route('campaignManager.index')->with('flash_message','Status changed successfully');
    }
}
