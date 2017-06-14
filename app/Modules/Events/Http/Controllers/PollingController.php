<?php

namespace App\Modules\Events\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Modules\Events\Models\PollingAnswer;
use App\Modules\Events\Models\PollingQuestion;
use App\Modules\Events\Models\Sessions;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Modules\Events\Http\Requests;

class PollingController extends Controller
{
    public function index()
    {
        $polling_questions = PollingQuestion::with('question_session')->with('poll_answers')->orderBy('id','DESC')->paginate();
        $sessions = Sessions::get();

        return view('events::polling.index',compact('polling_questions', 'sessions'));
    }

    public function create()
    {
        $sessions = Sessions::pluck('name','id')->toArray();
        return view('events::polling.create',compact('sessions'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'session_id' => 'required',
            'question' => 'required',
            'answer_A'  => 'required',
            'answer_B'  => 'required',
            'answer_C'  => 'required',
            'answer_D'  => 'required',
        ]);

        $pollquestion = new PollingQuestion();
        $pollquestion->question   = $request->question;
        $pollquestion->session_id = $request->session_id;
        $pollquestion->status     = $request->status;
        $pollquestion->created_at = Carbon::now();
        $pollquestion->updated_at = Carbon::now();
        $pollquestion->save();

        $PollingQuestion = PollingQuestion::find($pollquestion->id);
        $PollingQuestion->poll_answers()->saveMany([
            new PollingAnswer(['answer' => $request->answer_A,'session_id' => $request->session_id,'answer_index'=>'A','status'=>'1']),
            new PollingAnswer(['answer' => $request->answer_B,'session_id' => $request->session_id,'answer_index'=>'B','status'=>'1']),
            new PollingAnswer(['answer' => $request->answer_C,'session_id' => $request->session_id,'answer_index'=>'C','status'=>'1']),
            new PollingAnswer(['answer' => $request->answer_D,'session_id' => $request->session_id,'answer_index'=>'D','status'=>'1']),
        ]);

        // Store in audit
        $auditData                = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'pollquestion';
        $auditData['action']      = 'pollquestion.create';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);

        return redirect()->route('pollquestion.index')->with('flash_message','Question created successfully');
    }

    public function edit($id)
    {
        $polling_question = PollingQuestion::with('poll_answers')->find($id);
        $sessions = Sessions::pluck('name','id')->toArray();
        return view('events::polling.edit',compact('polling_question','sessions'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'session_id' => 'required',
            'question' => 'required',
            'answer_A'  => 'required',
            'answer_B'  => 'required',
            'answer_C'  => 'required',
            'answer_D'  => 'required',
        ]);

        $pollquestion = PollingQuestion::find($id)->update($request->all());
        PollingAnswer::where('question_id',$id)->delete();
        $PollingQuestion = PollingQuestion::find($id);
        $PollingQuestion->poll_answers()->saveMany([
            new PollingAnswer(['answer' => $request->answer_A,'session_id' => $request->session_id,'answer_index'=>'A','status'=>'1']),
            new PollingAnswer(['answer' => $request->answer_B,'session_id' => $request->session_id,'answer_index'=>'B','status'=>'1']),
            new PollingAnswer(['answer' => $request->answer_C,'session_id' => $request->session_id,'answer_index'=>'C','status'=>'1']),
            new PollingAnswer(['answer' => $request->answer_D,'session_id' => $request->session_id,'answer_index'=>'D','status'=>'1']),
        ]);

        // Store in audit
        $auditData                = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'pollquestion';
        $auditData['action']      = 'pollquestion.edit';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);

        return redirect()->route('pollquestion.index')->with('flash_message','Question updated successfully');
    }

    public function destroy(Request $request)
    {
        $Input       = $request->all();
        $pagesIDs    = $Input['pageIDs'];
        $pageIdArray = explode(',', $pagesIDs);

        foreach ($pageIdArray as $key => $pageId){
            PollingQuestion::find($pageId)->delete();
            PollingAnswer::where('question_id',$pageId)->delete();

            // Store in audit
            $auditData                = array();
            $auditData['user_id']     = \Auth::user()->id;
            $auditData['section']     = 'pollquestion';
            $auditData['action']      = 'pollquestion.delete';
            $auditData['time_stamp']  = time();
            $auditData['device_id']   = \Request::ip();
            $auditData['device_type'] = 'web';
            auditTrackRecord($auditData);

        }

        return redirect()->route('pollquestion.index')->with('flash_message','Question deleted successfully');
    }

    public function changeStatus(Request $request, $id)
    {

        $Input      = $request->all();
        $statusMode = $Input['mode'];
        $pollquestion    = PollingQuestion::find($id);

        if ($statusMode == 'unpublish'){
            $pollquestion->status = 0;
        }else{
            $pollquestion->status = 1;
        }
        $pollquestion->save();

        // Store in audit
        $auditData                = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'pollquestion';
        $auditData['action']      = 'pollquestion.changeStatus';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);

        return redirect()->route('pollquestion.index')->with('flash_message','Status changed successfully');
    }
}
