<?php

namespace App\Modules\Events\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Modules\Events\Models\Events;
use App\Modules\Events\Models\SessionsSpeakers;
use App\Modules\Events\Models\User;
use Illuminate\Http\Request;
use App\Modules\Events\Http\Requests;
use App\Modules\Events\Models\Sessions;
use Yajra\Datatables\Facades\Datatables;
use Carbon\Carbon;

class SessionsController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		$sessions = Sessions::with('session_sessions_speakers')->with('session_sessions_speakers.sessions_speakers_users')->with('events')->orderBy('id','DESC')->paginate();

		return view('events::sessions.index',compact('sessions'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		$events = Events::pluck('name','eventid')->toArray();
		$speakers = User::pluck('name','id')->toArray();
		return view('events::sessions.create',compact('events','speakers'));
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
			'name'        => 'required',
			'description' => 'required',
			'event_id'    => 'required',
			'date'        => 'required',
			'start_time'  => 'required',
			'end_time'    => 'required',
			'capacity'    => 'required',
			'venue'       => 'required',
			'type'        => 'required'
		]);

		$session = new Sessions();
		$session->name        = $request->name;
		$session->description = $request->description;
		$session->status      = $request->status;
		$session->start_time  = $request->start_time;
		$session->end_time    = $request->end_time;
		$session->event_id    = $request->event_id;
		$session->date        = $request->date;
		$session->capacity    = $request->capacity;
		$session->type        = $request->type;
		$session->venue       = $request->venue;
		$session->created_at  = Carbon::now();
		$session->updated_at  = Carbon::now();
        $session->save();

		$speaker_array = array();
		for ($i=0;$i<count($request->speaker_id);$i++)
		{
			$speaker_array[] = array('session_id'=>$session->id,'speaker_id'=>$request->speaker_id[$i]);
		}
		SessionsSpeakers::insert($speaker_array);

		// Store in audit
        $auditData                = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'sessions';
        $auditData['action']      = 'sessions.create';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);
		return redirect()->route('sessions.index')->with('flash_message','Session created successfully');
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		$session  = Sessions::with('session_sessions_speakers')->with('session_sessions_speakers.sessions_speakers_users')->with('events')->find($id);
		$events   = Events::pluck('name','eventid')->toArray();
		$speakers = User::pluck('name','id')->toArray();

		return view('events::sessions.edit',compact('session','events','speakers'));
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
			'name'        => 'required',
			'description' => 'required',
			'event_id'    => 'required',
			'date'        => 'required',
			'start_time'  => 'required',
			'end_time'    => 'required',
			'capacity'    => 'required',
			'venue'       => 'required',
			'type'        => 'required'
		]);

		Sessions::find($id)->update($request->all());
		$speaker_array = array();
		for ($i=0;$i<count($request->speaker_id);$i++)
		{
			$speaker_array[] = array('session_id'=>$id,'speaker_id'=>$request->speaker_id[$i]);
		}
		SessionsSpeakers::where('session_id',$id)->delete();
		SessionsSpeakers::insert($speaker_array);

		// Store in audit
        $auditData                = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'sessions';
        $auditData['action']      = 'sessions.edit';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);

        return redirect()->route('sessions.index')->with('flash_message','Session updated successfully');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Request $request)
	{
		$Input       = $request->all();
		$pagesIDs    = $Input['pageIDs'];
		$pageIdArray = explode(',', $pagesIDs);

		foreach ($pageIdArray as $key => $pageId){
			Sessions::find($pageId)->delete();

			// Store in audit
	        $auditData                = array();
	        $auditData['user_id']     = \Auth::user()->id;
	        $auditData['section']     = 'sessions';
	        $auditData['action']      = 'sessions.delete';
	        $auditData['time_stamp']  = time();
	        $auditData['device_id']   = \Request::ip();
	        $auditData['device_type'] = 'web';
	        auditTrackRecord($auditData);
		}

        return redirect()->route('sessions.index')->with('flash_message','Session deleted successfully');
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
		$Sessions    = Sessions::find($id);

		if ($statusMode == 'unpublish'){
			$Sessions->status = 0;
		}else{
			$Sessions->status = 1;
		}
		$Sessions->save();

		// Store in audit
        $auditData                = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'sessions';
        $auditData['action']      = 'sessions.changeStatus';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);


        return redirect()->route('sessions.index')->with('flash_message','Status changed successfully');
	}
}
