<?php

namespace App\Modules\Events\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Modules\Events\Models\Feedback;
use App\Modules\Events\Models\Sessions;
use App\Modules\Events\Models\User;
use Illuminate\Http\Request;

use App\Modules\Events\Http\Requests;

class FeedbackController extends Controller
{
    public function index()
    {
        $session_feedbacks = Sessions::with('session_feedback','user')->whereHas('session_feedback', function($q)
        {
            $q->where('id','!=',NULL);

        })->orderBy('id','DESC')->paginate();
        $users = User::get();

        return view('events::feedback.index',compact('session_feedbacks', 'users'));
    }

    public function destroy(Request $request)
    {
        $Input       = $request->all();
        $pagesIDs    = $Input['pageIDs'];
        $pageIdArray = explode(',', $pagesIDs);

        foreach ($pageIdArray as $key => $pageId){
            Feedback::find($pageId)->delete();

            // Store in audit
            $auditData                = array();
            $auditData['user_id']     = \Auth::user()->id;
            $auditData['section']     = 'feedback';
            $auditData['action']      = 'feedback.delete';
            $auditData['time_stamp']  = time();
            $auditData['device_id']   = \Request::ip();
            $auditData['device_type'] = 'web';
            auditTrackRecord($auditData);
        }
        return redirect()->route('feedback.index')->with('flash_message','Feedback deleted successfully');
    }

    public function changeStatus(Request $request, $id)
    {

        $Input      = $request->all();
        $statusMode = $Input['mode'];
        $pollquestion    = Feedback::find($id);

        if ($statusMode == 'unpublish'){
            $pollquestion->status = 0;
        }else{
            $pollquestion->status = 1;
        }
        $pollquestion->save();

        // Store in audit
        $auditData                = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'feedback';
        $auditData['action']      = 'feedback.changeStatus';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);


        return redirect()->route('feedback.index')->with('flash_message','Status changed successfully');
    }
}
