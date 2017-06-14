<?php

namespace App\Modules\Events\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Modules\Events\Http\Requests;

use App\Modules\Events\Models\UserEvents;
use App\Modules\Events\Models\Events;
use App\Modules\Events\Models\User;

class UserRequestForEventController extends Controller
{
    public function index(Request $request) {
    	$userRequestForEvent = UserEvents::with('userevent_user')->with('userevent_event')->orderBy('id','DESC')->paginate();
    	$events = Events::get();
    	$users = User::get();

		return view('events::userRequestForEvent.index',compact('userRequestForEvent', 'events', 'users'));
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

		$Input               = $request->all();
		$statusMode          = $Input['mode'];
		$userRequestForEvent = UserEvents::find($id);

		if ($statusMode == 'unpublish'){
			$userRequestForEvent->status = 0;
		}else{
			$userRequestForEvent->status = 1;
		}
		$userRequestForEvent->save();

		// Store in audit
        $auditData                = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'event';
        $auditData['action']      = 'event.userRequestForEvent';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);

        return redirect()->route('UserRequestForEvent.index')->with('flash_message','Status changed successfully');
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
			UserEvents::find($pageId)->delete();

			// Store in audit
	        $auditData = array();
	        $auditData['user_id'] = \Auth::user()->id;
	        $auditData['section'] = 'event';
	        $auditData['action'] = 'userRequestForEvent.delete';
	        $auditData['time_stamp'] = time();
	        $auditData['device_id'] = \Request::ip();
	        $auditData['device_type'] = 'web';
	        auditTrackRecord($auditData);
		}

        return redirect()->route('UserRequestForEvent.index')->with('flash_message','Item deleted successfully');
	}
}
