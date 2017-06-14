<?php

namespace App\Modules\Events\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Events\Models\Question;
use App\Modules\Events\Models\Sessions;
use App\Modules\Events\Models\User;
use Illuminate\Http\Request;

use App\Modules\Events\Http\Requests;

use Yajra\Datatables\Facades\Datatables;

class QuestionController extends Controller
{
    public function index()
    {
        $session_questions = Sessions::with('session_question','user')->whereHas('session_question', function($q)
        {
            $q->where('id','!=',NULL);

        })->orderBy('id','DESC')->paginate();
        $sessions = Sessions::get();
        $users = User::get();

        return view('events::question.index',compact('session_questions','sessions', 'users'));
    }

    public function QuestionData(Request $request)
    {
        $provinces = Sessions::with('session_question','user')->whereHas('session_question', function($q)
        {
            $q->where('id','!=',NULL);

        });

        return Datatables::of($provinces)->make(true);
    }

    public function destroy(Request $request)
    {
        $Input       = $request->all();
        $pagesIDs    = $Input['pageIDs'];
        $pageIdArray = explode(',', $pagesIDs);

        foreach ($pageIdArray as $key => $pageId){
            Question::find($pageId)->delete();

            // Store in audit
            $auditData                = array();
            $auditData['user_id']     = \Auth::user()->id;
            $auditData['section']     = 'question';
            $auditData['action']      = 'question.delete';
            $auditData['time_stamp']  = time();
            $auditData['device_id']   = \Request::ip();
            $auditData['device_type'] = 'web';
            //auditTrackRecord($auditData);
        }
        return redirect()->route('question.index')->with('flash_message','Question deleted successfully');
    }

    public function changeStatus(Request $request, $id)
    {

        $Input      = $request->all();
        $statusMode = $Input['mode'];
        $pollquestion    = Question::find($id);

        if ($statusMode == 'unpublish'){
            $pollquestion->status = 0;
        }else{
            $pollquestion->status = 1;
        }
        $pollquestion->save();

        // Store in audit
        $auditData                = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'question';
        $auditData['action']      = 'question.changeStatus';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        //auditTrackRecord($auditData);


        return redirect()->route('question.index')->with('flash_message','Status changed successfully');
    }
}
