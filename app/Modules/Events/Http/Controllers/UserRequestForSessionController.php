<?php

namespace App\Modules\Events\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Modules\Events\Http\Requests;

use App\Modules\Events\Models\UserRequestForSession;
use App\Modules\Events\Models\Sessions;
use App\Modules\Events\Models\User;

class UserRequestForSessionController extends Controller
{
    public function index(Request $request) {
    	$userRequestForSession = UserRequestForSession::with('user_request_for_session_session')->with('user_request_for_session_user')->orderBy('id','DESC')->paginate();
    	$sessions = Sessions::get();
        $users = User::get();

		return view('events::userRequestForSession.index',compact('userRequestForSession', 'sessions', 'users'));
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
		$UserRequestForSession   = UserRequestForSession::find($id);

		if ($statusMode == 'unpublish'){
			$UserRequestForSession->status = 0;
		}else{
			$UserRequestForSession->status = 1;
		}
		$UserRequestForSession->save();

		// Store in audit
        $auditData                = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'sessions';
        $auditData['action']      = 'sessions.userRequestForSession';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);

        return redirect()->route('UserRequestForSession.index')->with('flash_message','Status changed successfully');
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
			UserRequestForSession::find($pageId)->delete();

			// Store in audit
	        $auditData = array();
	        $auditData['user_id'] = \Auth::user()->id;
	        $auditData['section'] = 'session';
	        $auditData['action'] = 'userRequestForSession.delete';
	        $auditData['time_stamp'] = time();
	        $auditData['device_id'] = \Request::ip();
	        $auditData['device_type'] = 'web';
	        auditTrackRecord($auditData);
		}

        return redirect()->route('UserRequestForSession.index')->with('flash_message','Item deleted successfully');
	}
}
