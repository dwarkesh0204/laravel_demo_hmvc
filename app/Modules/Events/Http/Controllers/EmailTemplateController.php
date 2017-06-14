<?php

namespace App\Modules\Events\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Modules\Events\Http\Requests;

use App\Modules\Events\Models\EmailTemplate;

use Carbon\Carbon;

use Yajra\Datatables\Facades\Datatables;

class EmailTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = EmailTemplate::orderBy('id','ASC')->paginate();

        return view('events::email_template.index',compact('items'));
    }

    public function EmailTemplateData(Request $request)
    {
        $provinces = EmailTemplate::select('*');

        return Datatables::of($provinces)->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('events::email_template.create');
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
            'title'     => 'required',
            'subject'   => 'required',
            'message'   => 'required',
            'from'      => 'required',
            'from_name' => 'required'
        ]);

        $input = $request->all();

        if($input['status'] == 1)
        {
            EmailTemplate::where('status',1)->update((array('status' => "0")));
        }

        $emailTemplate = new EmailTemplate();
        $emailTemplate->title         = $input['title'];
        $emailTemplate->subject       = $input['subject'];
        $emailTemplate->message       = $input['message'];
        $emailTemplate->type          = $input['type'];
        $emailTemplate->tags          = $input['tags'];
        $emailTemplate->from          = $input['from'];
        $emailTemplate->from_name     = $input['from_name'];
        $emailTemplate->reply_to      = $input['reply_to'];
        $emailTemplate->reply_to_name = $input['reply_to_name'];
        $emailTemplate->created_by    = $input['created_by'];
        $emailTemplate->status        = $input['status'];
        $emailTemplate->created_at    = Carbon::now();
        $emailTemplate->updated_at    = Carbon::now();
        $emailTemplate->save();

        return redirect()->route('email_template.index')->with('flash_message','Email Template created successfully');
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
        $item = EmailTemplate::find($id);

        return view('events::email_template.edit',compact('item'));
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

        if($input['status'] == 1)
        {
            EmailTemplate::where('status',1)->update((array('status' => "0")));
        }

        EmailTemplate::find($id)->update($input);

        return redirect()->route('email_template.index')->with('flash_message','Email Template updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $Input = $request->all();
        $emailtmpIDs     = $Input['emailtmpIDs'];
        $emailtmpIDArray = explode(',', $emailtmpIDs);

        foreach ($emailtmpIDArray as $key => $emailtmpID)
        {
            EmailTemplate::find($emailtmpID)->delete();
        }

        return redirect()->route('email_template.index')->with('flash_message','Email Template deleted successfully');
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
        $EmailTemplate    = EmailTemplate::find($id);
        $EmailTemplate    = EmailTemplate::find($id);

        if ($statusMode == 1)
        {
            EmailTemplate::where('status',1)->update((array('status' => "0")));
            $EmailTemplate->status = 1;
            $message = "Status changed successfully";
        }
        else
        {
            $EmailTemplate->status = 1;
            $message = "This template already seted as a defalut";
        }

        /*if ($statusMode == 0)
        {
            $EmailTemplate->status = 0;
        }
        else
        {
            $EmailTemplate->status = 1;
        }*/

        $EmailTemplate->save();

        return redirect()->route('email_template.index')->with('flash_message',$message);
    }
}
