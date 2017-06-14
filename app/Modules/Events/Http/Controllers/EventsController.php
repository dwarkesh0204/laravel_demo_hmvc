<?php

namespace App\Modules\Events\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Modules\Events\Http\Requests;
use App\Modules\Events\Models\Events;
use App\Modules\Events\Models\EventField;
use App\Modules\Events\Models\Media;
use App\Modules\Events\Models\EventTickets;
use App\Modules\Events\Models\Role;

use Illuminate\Support\Facades\Storage;

use Intervention\Image\ImageManagerStatic as Image;
use Carbon\Carbon;
use DB;

use Yajra\Datatables\Facades\Datatables;

class EventsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $events = Events::orderBy('eventid','DESC')->paginate();
        return view('events::events.index',compact('events'));
    }

    public function EventsData(Request $request)
    {
        $provinces = Events::select('*');

        return Datatables::of($provinces)->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles =  Role::orderBy('id','DESC')->get();
        return view('events::events.create',compact('roles'));
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
            'name'      => 'required',
            'startdate' => 'required',
            'starttime' => 'required',
            'enddate'   => 'required',
            'endtime'   => 'required',
            'venue'     => 'required',
            'roles'     => 'required'
        ]);

        $input = $request->all();
        $event = new Events();
        $event->name                = $input['name'];
        $event->shortdesc           = $input['shortdesc'];
        $event->description         = $input['description'];
        $event->status              = $input['status'];
        $event->startdate           = $input['startdate'];
        $event->starttime           = $input['starttime'];
        $event->enddate             = $input['enddate'];
        $event->endtime             = $input['endtime'];
        $event->addedby             = Auth::id();
        $event->type                = $input['type'];
        $event->price               = (!empty($input['price'])) ? $input['price'] : 0;
        $event->expected_attendaees = (!empty($input['expected_attendaees'])) ? $input['expected_attendaees'] : 0;
        $event->venue               = $input['venue'];
        $event->phone_number        = $input['phone_number'];
        $event->email               = $input['email'];
        $event->latitude            = $input['latitude'];
        $event->longitude           = $input['longitude'];
        $event->entrypass           = implode(",",$input['roles']);
        $event->pagetype            = $input['pagetype'];
        $event->url                 = $input['url'];
        $event->html                = $input['html'];
        $event->meals               = $input['meals'];
        $event->roles               = implode(",",$input['roles']);
        $event->created_at          = Carbon::now();
        $event->updated_at          = Carbon::now();
        $event->save();

        $eventId = $event->getAttribute('eventid');

        if($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');

            // Create Directory
            Storage::disk('upload')->makeDirectory('Events/'.$eventId);

            $storage = public_path()."/uploads/Events/".$eventId;

            // Create A FileName From Uploaded File.
            $fileName = time().$request->file('cover_image')->getClientOriginalName();

            // Remove Spaces In The Filename And Convert To LowerCase.
            $fileName = str_replace(' ', '', strtolower($fileName));

            // Move The File To Storage Directory
            if($request->file('cover_image')->move($storage,$fileName)){
                $FileNameStore      = "/uploads/Events/".$eventId."/".$fileName;
                $event              = Events::find($eventId);
                $event->cover_image = $FileNameStore;
                $event->save();
            }
        }

        /*
        $fields = $request->input('fields');
        if($fields[0]['display_name'])
        {
            foreach ($fields as $key => $value)
            {
                $value['field_name'] = 'field_'.strtolower(str_replace(" ", "_", $value['display_name']));
                $value['event_id']   = $event->eventid;
                $value['updated_at'] = date('Y-m-d h:i:s');
                $value['created_at'] = date('Y-m-d h:i:s');
                $fields[$key] = $value;
            }
            EventField::insert($fields);
        }
        */

        // Store in audit
        $auditData                = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'event';
        $auditData['action']      = 'event.create';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);

        return redirect()->route('events.index')->with('flash_message','Event created successfully');
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item = Events::find($id);
        $eventfields  = EventField::where("event_id", $id)->get();
        $eventtickets = EventTickets::where("event_id", $id)->get();
        $roles        =  Role::orderBy('id','DESC')->get();
        $eventRoles   = explode(",",$item->roles);

        return view('events::events.edit',compact('item','eventfields','eventtickets','roles','eventRoles'));
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
            'name'      => 'required',
            'startdate' => 'required',
            'starttime' => 'required',
            'enddate'   => 'required',
            'endtime'   => 'required',
            'venue'     => 'required',
            'roles'     => 'required'
        ]);

        if($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');

            // Create Directory
            Storage::disk('upload')->makeDirectory('Events/'.$id);

            $storage = public_path()."/uploads/Events/".$id;

            // Create A FileName From Uploaded File.
            $fileName = time().$request->file('cover_image')->getClientOriginalName();

            // Remove Spaces In The Filename And Convert To LowerCase.
            $fileName = str_replace(' ', '', strtolower($fileName));

            // Move The File To Storage Directory
            if($request->file('cover_image')->move($storage,$fileName)){

                $FileNameStore      = "/uploads/Events/".$id."/".$fileName;
                $event              = Events::find($id);
                $event->cover_image = $FileNameStore;
                $event->save();
            }
        }

        $input          = $request->all();
        $input['roles'] = implode(",",$input['roles']);
        Events::find($id)->update($input);

        /*
        $fields = $request->input('fields');

        foreach ($fields as $key => $value) {

            if($value['display_name'])
            {
                if(!empty($value['id']))
                {

                    $value['updated_at'] = date('Y-m-d h:i:s');
                    EventField::where('id', $value['id'])->update(array_except($value,'id'));
                }
                else
                {
                    $value['field_name'] = 'field_'.strtolower(str_replace(" ", "_", $value['display_name']));
                    $value['event_id'] = $id;
                    $value['updated_at'] = date('Y-m-d h:i:s');
                    $value['created_at'] = date('Y-m-d h:i:s');
                    EventField::insert($value);
                }
            }
        }
        $deletedFieldId = $request->input('deletedFieldId');

        if($deletedFieldId)
        {
            $deletedFieldIds = explode(",",$request->input('deletedFieldId'));

            if(count($deletedFieldIds) > 0)
            {
                foreach ($deletedFieldIds as $key => $deletedFieldId)
                {
                    EventField::find($deletedFieldId)->delete();
                }
            }
        }
        */


        // Store in audit
        $auditData                = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'event';
        $auditData['action']      = 'event.edit';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);

        return redirect()->route('events.index')->with('flash_message','Event updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $Input        = $request->all();
        $eventsIDs    = $Input['pageIDs'];
        $eventIdArray  = explode(',', $eventsIDs);

        foreach ($eventIdArray as $key => $eventId){
            Events::find($eventId)->delete();

            // Store in audit
            $auditData                = array();
            $auditData['user_id']     = \Auth::user()->id;
            $auditData['section']     = 'event';
            $auditData['action']      = 'event.delete';
            $auditData['time_stamp']  = time();
            $auditData['device_id']   = \Request::ip();
            $auditData['device_type'] = 'web';
            auditTrackRecord($auditData);
        }

        return redirect()->route('events.index')->with('flash_message','Event deleted successfully');
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
        $statusMode = $request->get('mode');
        $Event      = Events::find($id);

        if ($statusMode == 'unpublish'){
            $Event->status = 0;
        }else{
            $Event->status = 1;
        }
        $Event->save();

        // Store in audit
        $auditData                = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'event';
        $auditData['action']      = 'event.changeStatus';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);

        return redirect()->route('events.index')->with('flash_message','Status changed successfully');
    }

    /**
     * Delete Cover Image.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteCover(Request $request)
    {
        $Input      = $request->all();
        $eventId    = $Input['eventId'];
        $Event      = Events::find($eventId);

        $imagePath = $Event['cover_image'];
        unlink(public_path().$imagePath);
        $Event['cover_image'] = "";
        $Event->save();

        return redirect()->back()->with('flash_message','Cover deleted successfully');
    }

    public function ImageAction(Request $request, $id)
    {
        $tabId = $request->get('tabid');

        if($tabId == "event_images")
        {
            if($request->hasFile('uploaded_files'))
            {
                $validator = $this->validate($request, [
                    'uploaded_files'      => 'required | mimes:jpeg,jpg,png | max:20000'
                ]);

                $file = $request->file('uploaded_files');

                // Create Directory
                Storage::disk('upload')->makeDirectory('Events/'.$id.'/images');

                $storage = public_path()."/uploads/Events/".$id."/images";

                // Create A FileName From Uploaded File.
                $fileName = time().$request->file('uploaded_files')->getClientOriginalName();

                // Remove Spaces In The Filename And Convert To LowerCase.
                $fileName = str_replace(' ', '', strtolower($fileName));

                // Move The File To Storage Directory
                if($request->file('uploaded_files')->move($storage,$fileName))
                {
                    $FileNameStore      = "/uploads/Events/".$id."/images/".$fileName;
                    $thumbFileNameStore = "/uploads/Events/".$id."/images/thumb_".$fileName;
                    //$ThumbFileNameStore       = "/uploads/Events/".$id."/images/thumb_".$fileName;
                    $ThumbFileNameCreate = public_path('uploads/Events/'.$id.'/images/thumb_'.$fileName);

                    $img = Image::make(asset($FileNameStore));

                    // now you are able to resize the instance
                    $img->resize(300, 200);

                    // finally we save the image as a new file
                    $img->save($ThumbFileNameCreate);

                    $media                    = array();
                    $media['event_id']        = $id;
                    $media['file_name']       = $fileName;
                    $media['file_path']       = $FileNameStore;
                    $media['thumb_file_path'] = $thumbFileNameStore;
                    $media['file_type']       = 'image';
                    $media['updated_at']      = date('Y-m-d h:i:s');
                    $media['created_at']      = date('Y-m-d h:i:s');
                    Media::insert($media);

                    // Store in audit
                    $auditData                = array();
                    $auditData['user_id']     = \Auth::user()->id;
                    $auditData['section']     = 'event';
                    $auditData['action']      = 'event.imageUpload';
                    $auditData['time_stamp']  = time();
                    $auditData['device_id']   = \Request::ip();
                    $auditData['device_type'] = 'web';
                    auditTrackRecord($auditData);

                    return redirect()->back()->with('flash_message','Image Uploaded successfully');
                }
            }
        }
        else if($tabId == "event_videos")
        {
            if($request->hasFile('uploaded_files'))
            {
                $this->validate($request, [
                'uploaded_files'      => 'required | mimes:3gp,mp4,avi,mov,mpeg | max:300000'
                ]);

                $file = $request->file('uploaded_files');

                // Create Directory
                Storage::disk('upload')->makeDirectory('Events/'.$id.'/videos');

                $storage = public_path()."/uploads/Events/".$id."/videos";

                // Create A FileName From Uploaded File.
                $fileName = time().$request->file('uploaded_files')->getClientOriginalName();

                // Remove Spaces In The Filename And Convert To LowerCase.
                $fileName = str_replace(' ', '', strtolower($fileName));

                // Move The File To Storage Directory
                if($request->file('uploaded_files')->move($storage,$fileName))
                {
                    $FileNameStore = "/uploads/Events/".$id."/videos/".$fileName;
                    /*$thumbFileNameStore       = "/uploads/Events/".$id."/videos/thumb_".$fileName;
                    $ThumbFileNameCreate = public_path('uploads/Events/'.$id.'/videos/thumb_'.$fileName);
                    $img = Image::make(asset($FileNameStore));
                    $img->resize(300, 200);
                    $img->save($ThumbFileNameCreate);*/

                    $media                    = array();
                    $media['event_id']        = $id;
                    $media['file_name']       = $fileName;
                    $media['file_path']       = $FileNameStore;
                    $media['thumb_file_path'] = '';
                    $media['file_type']       = 'video';
                    $media['updated_at']      = date('Y-m-d h:i:s');
                    $media['created_at']      = date('Y-m-d h:i:s');
                    Media::insert($media);

                    // Store in audit
                    $auditData                = array();
                    $auditData['user_id']     = \Auth::user()->id;
                    $auditData['section']     = 'event';
                    $auditData['action']      = 'event.videoUpload';
                    $auditData['time_stamp']  = time();
                    $auditData['device_id']   = \Request::ip();
                    $auditData['device_type'] = 'web';
                    auditTrackRecord($auditData);

                    return redirect()->back()->with('flash_message','video Uploaded successfully');
                }
            }
        }
        else if($tabId == "event_documents")
        {
            if($request->hasFile('uploaded_files'))
            {
                $this->validate($request, [
                'uploaded_files' => 'required | mimes:pdf,doc,docx,xls,csv,txt,ppt | max:30000'
                ]);

                $file = $request->file('uploaded_files');

                // Create Directory
                Storage::disk('upload')->makeDirectory('Events/'.$id.'/documents');

                $storage = public_path()."/uploads/Events/".$id."/documents";

                // Create A FileName From Uploaded File.
                $fileName = time().$request->file('uploaded_files')->getClientOriginalName();

                // Remove Spaces In The Filename And Convert To LowerCase.
                $fileName = str_replace(' ', '', strtolower($fileName));

                // Move The File To Storage Directory
                if($request->file('uploaded_files')->move($storage,$fileName))
                {
                    $FileNameStore = "/uploads/Events/".$id."/documents/".$fileName;

                    $media                    = array();
                    $media['event_id']        = $id;
                    $media['file_name']       = $fileName;
                    $media['file_path']       = $FileNameStore;
                    $media['thumb_file_path'] = '';
                    $media['file_type']       = 'document';
                    $media['updated_at']      = date('Y-m-d h:i:s');
                    $media['created_at']      = date('Y-m-d h:i:s');
                    Media::insert($media);

                    // Store in audit
                    $auditData                = array();
                    $auditData['user_id']     = \Auth::user()->id;
                    $auditData['section']     = 'event';
                    $auditData['action']      = 'event.documentUpload';
                    $auditData['time_stamp']  = time();
                    $auditData['device_id']   = \Request::ip();
                    $auditData['device_type'] = 'web';
                    auditTrackRecord($auditData);

                    return redirect()->back()->with('flash_message','Document Uploaded successfully');
                }
            }
        }

    }


    public function ImageUrlAction(Request $request,$id)
    {
        $result = 0;
        $tabId = $request->get('tabId');

        if($tabId == "event_images")
        {
            if($request->uploadtype == 'google-drive')
            {
                $files = $request['files'];
                $files_count = count($files);
                for($i=0;$i<$files_count;$i++)
                {
                    $file_name = time() . "-" . $files[$i]['name'];

                    $url = 'https://www.googleapis.com/drive/v2/files/' . urlencode($files[$i]['id']) . '?alt=media';
                    $oAuthToken = $request->oauthToken;
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    // If google drive file download, we need the token
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $oAuthToken]);
                    $data = curl_exec($ch);
                    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $error = curl_errno($ch);
                    curl_close($ch);

                    $final_filename = $document_path.$file_name;
                    $aws_object = new AwsActions();
                    $s3_result = $aws_object->UploadBody($BucketName,$data,$final_filename);
                    if($s3_result['ObjectURL'] != '')
                    {
                        $new_s3_filepath = 'https://s3-eu-west-1.amazonaws.com/' . $BucketName . '/' . $final_filename;
                        $ActionData['FilePath'] = $new_s3_filepath;
                        $ActionData['FileS3Key'] = $file_name;
                        $result_final = DB::connection($this->MysqlSchemaName)->table($this->DocumentsTable)->insertGetId($ActionData);
                        $result = 1;
                    }
                }
            }
            else
            {
                $files = $request['files'];
                $files_count = count($files);
                for($i=0;$i<$files_count;$i++)
                {
                    $extension_array = explode('.', trim($files[$i]['name']));
                    $fileType = $extension_array[count($extension_array)-1];

                    $result = '';
                    if($fileType != 'jpeg' && $fileType != 'jpg' && $fileType != 'png')
                    {
                        $result = 0;
                        $message = 'Please upload only jpeg, jpg and png image.';
                        return \Response::json(['success'=>$result,'message'=>$message]);
                    }

                    $fileurl       = $files[$i]['link'];
                    $file_name     = time() . "-" . $files[$i]['name'];
                    $file_data     = file_get_contents($fileurl);
                    $FileNameStore = "/uploads/Events/".$id."/images/".$file_name;
                    file_put_contents(public_path().$FileNameStore, $file_data);

                    $thumbFileNameStore = "/uploads/Events/".$id."/images/thumb_".$file_name;
                        //$ThumbFileNameStore       = "/uploads/Events/".$id."/images/thumb_".$fileName;
                    $ThumbFileNameCreate = public_path('uploads/Events/'.$id.'/images/thumb_'.$file_name);

                    $img = Image::make(asset($FileNameStore));

                        // now you are able to resize the instance
                    $img->resize(300, 200);

                        // finally we save the image as a new file
                    $img->save($ThumbFileNameCreate);

                    $media                    = array();
                    $media['event_id']        = $id;
                    $media['file_name']       = $file_name;
                    $media['file_path']       = $FileNameStore;
                    $media['thumb_file_path'] = $thumbFileNameStore;
                    $media['file_type']       = 'image';
                    $media['updated_at']      = date('Y-m-d h:i:s');
                    $media['created_at']      = date('Y-m-d h:i:s');
                    Media::insert($media);

                    // Store in audit
                    $auditData                = array();
                    $auditData['user_id']     = \Auth::user()->id;
                    $auditData['section']     = 'event';
                    $auditData['action']      = 'event.imageUpload';
                    $auditData['time_stamp']  = time();
                    $auditData['device_id']   = \Request::ip();
                    $auditData['device_type'] = 'web';
                    auditTrackRecord($auditData);

                    $result = 1;
                    $message = "success";
                }
            }
        }
        else if($tabId == "event_videos")
        {
            if($request->uploadtype == 'google-drive')
            {
                $files = $request['files'];
                $files_count = count($files);
                for($i=0;$i<$files_count;$i++)
                {
                    $file_name = time() . "-" . $files[$i]['name'];

                    $url = 'https://www.googleapis.com/drive/v2/files/' . urlencode($files[$i]['id']) . '?alt=media';
                    $oAuthToken = $request->oauthToken;
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    // If google drive file download, we need the token
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $oAuthToken]);
                    $data = curl_exec($ch);
                    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $error = curl_errno($ch);
                    curl_close($ch);

                    $final_filename = $document_path.$file_name;
                    $aws_object = new AwsActions();
                    $s3_result = $aws_object->UploadBody($BucketName,$data,$final_filename);
                    if($s3_result['ObjectURL'] != '')
                    {
                        $new_s3_filepath = 'https://s3-eu-west-1.amazonaws.com/' . $BucketName . '/' . $final_filename;
                        $ActionData['FilePath'] = $new_s3_filepath;
                        $ActionData['FileS3Key'] = $file_name;
                        $result_final = DB::connection($this->MysqlSchemaName)->table($this->DocumentsTable)->insertGetId($ActionData);
                        $result = 1;
                    }
                }

            }
            else
            {
                $files = $request['files'];

                $files_count = count($files);
                for($i=0;$i<$files_count;$i++)
                {
                    $extension_array = explode('.', trim($files[$i]['name']));
                    $fileType = $extension_array[count($extension_array)-1];

                    $result = '';

                    if($fileType != '3gp' && $fileType != 'mp4' && $fileType != 'avi' && $fileType != 'mov' && $fileType != 'mpeg')
                    {
                        $result = 0;
                        $message = 'Please upload only 3gp, mp4, avi, mov, and mpeg video.';
                        return \Response::json(['success'=>$result,'message'=>$message]);
                    }

                    $result        = '';
                    $fileurl       = $files[$i]['link'];
                    $file_name     = time() . "-" . $files[$i]['name'];
                    $file_data     = file_get_contents($fileurl);
                    $FileNameStore = "/uploads/Events/".$id."/videos/".$file_name;
                    file_put_contents(public_path().$FileNameStore, $file_data);

                    /*$thumbFileNameStore       = "/uploads/Events/".$id."/images/thumb_".$file_name;
                    $ThumbFileNameCreate = public_path('uploads/Events/'.$id.'/images/thumb_'.$file_name);
                    $img = Image::make(asset($FileNameStore));
                    $img->resize(300, 200);
                    $img->save($ThumbFileNameCreate);*/

                    $media                    = array();
                    $media['event_id']        = $id;
                    $media['file_name']       = $file_name;
                    $media['file_path']       = $FileNameStore;
                    $media['thumb_file_path'] = "";
                    $media['file_type']       = 'video';
                    $media['updated_at']      = date('Y-m-d h:i:s');
                    $media['created_at']      = date('Y-m-d h:i:s');
                    Media::insert($media);

                    // Store in audit
                    $auditData                = array();
                    $auditData['user_id']     = \Auth::user()->id;
                    $auditData['section']     = 'event';
                    $auditData['action']      = 'event.videoUpload';
                    $auditData['time_stamp']  = time();
                    $auditData['device_id']   = \Request::ip();
                    $auditData['device_type'] = 'web';
                    auditTrackRecord($auditData);

                    $result = 1;
                    $message = "success";
                }
            }
        }
        else if($tabId == "event_documents")
        {
            if($request->uploadtype == 'google-drive')
            {
                $files = $request['files'];
                $files_count = count($files);
                for($i=0;$i<$files_count;$i++)
                {
                    $file_name = time() . "-" . $files[$i]['name'];

                    $url = 'https://www.googleapis.com/drive/v2/files/' . urlencode($files[$i]['id']) . '?alt=media';
                    $oAuthToken = $request->oauthToken;
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    // If google drive file download, we need the token
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $oAuthToken]);
                    $data = curl_exec($ch);
                    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $error = curl_errno($ch);
                    curl_close($ch);

                    $final_filename = $document_path.$file_name;
                    $aws_object = new AwsActions();
                    $s3_result = $aws_object->UploadBody($BucketName,$data,$final_filename);
                    if($s3_result['ObjectURL'] != '')
                    {
                        $new_s3_filepath = 'https://s3-eu-west-1.amazonaws.com/' . $BucketName . '/' . $final_filename;
                        $ActionData['FilePath'] = $new_s3_filepath;
                        $ActionData['FileS3Key'] = $file_name;
                        $result_final = DB::connection($this->MysqlSchemaName)->table($this->DocumentsTable)->insertGetId($ActionData);
                        $result = 1;
                    }
                }
            }
            else
            {
                $files = $request['files'];

                $files_count = count($files);
                for($i=0;$i<$files_count;$i++)
                {
                    $extension_array = explode('.', trim($files[$i]['name']));
                    $fileType = $extension_array[count($extension_array)-1];

                    $result = '';
                    if($fileType != 'pdf' && $fileType != 'doc' && $fileType != 'docx' && $fileType != 'xls' && $fileType != 'csv' && $fileType != 'txt' && $fileType != 'ppt')
                    {
                        $result = 0;
                        $message = 'Please upload only pdf, doc, docx, xls, csv, txt, and ppt Document.';
                        return \Response::json(['success'=>$result,'message'=>$message]);
                    }

                    $result = '';
                    $fileurl = $files[$i]['link'];
                    $file_name = time() . "-" . $files[$i]['name'];
                    $file_data = file_get_contents($fileurl);
                    $FileNameStore       = "/uploads/Events/".$id."/documents/".$file_name;
                    file_put_contents(public_path().$FileNameStore, $file_data);

                    $media                    = array();
                    $media['event_id']        = $id;
                    $media['file_name']       = $file_name;
                    $media['file_path']       = $FileNameStore;
                    $media['thumb_file_path'] = "";
                    $media['file_type']       = 'document';
                    $media['updated_at']      = date('Y-m-d h:i:s');
                    $media['created_at']      = date('Y-m-d h:i:s');
                    Media::insert($media);

                    // Store in audit
                    $auditData                = array();
                    $auditData['user_id']     = \Auth::user()->id;
                    $auditData['section']     = 'event';
                    $auditData['action']      = 'event.documentUpload';
                    $auditData['time_stamp']  = time();
                    $auditData['device_id']   = \Request::ip();
                    $auditData['device_type'] = 'web';
                    auditTrackRecord($auditData);

                    $result = 1;
                    $message = "success";
                }
            }
        }
        return \Response::json(['success'=>$result,'message'=>$message]);
    }


    public function GetUploadFileList(Request $request)
    {
        $eventId = $request->eventid;
        $tabId   =  $request->get('tabId');
        $success = 0;
        $data = '';

        if($tabId == "event_images")
        {
            $images = Media::where("media.event_id",$eventId)->where("media.file_type",'image')->get();
            $count_img = count($images);

            if($count_img > 0)
            {
                $data = '';

                for ($i=0; $i < $count_img; $i++)
                {
                    $data .= '<div class="col-md-3 col-sm-4 col-xs-6 image_list"><img class="img-responsive" src="'.asset($images[$i]->thumb_file_path).'" /><div class="edit"><a href="#" class="removeImage" imageId="'.$images[$i]->id.'"><i class="fa fa-times fa-2x"></i></a></div></div>';
                }
                $success = 1;
            }
            else
            {
                $data = 'No Images';
                $success = 0;
            }
        }
        else if($tabId == "event_videos")
        {
            $videos = Media::where("media.event_id",$eventId)->where("media.file_type",'video')->get();
            $count_vid = count($videos);

            if($count_vid > 0)
            {
                $data = '';
                for ($i=0; $i < $count_vid; $i++)
                {
                    $extension_array = explode('.', trim($videos[$i]->file_name));
                    $fileType = $extension_array[count($extension_array)-1];
                    $data .= '<div class="col-md-3 col-sm-4 col-xs-6"><video width="300" height="200" controls><source src="'.asset($videos[$i]->file_path).'" type="video/'.$fileType.'"></video><div class="edit"><a href="#" class="removeVideo" videoId="'.$videos[$i]->id.'"><i class="fa fa-times fa-2x"></i></a></div></div>';
                }
                $success = 1;
            }
            else
            {
                $data = 'No Videos';
                $success = 0;
            }
        }
        else if($tabId == "event_documents")
        {
            $documents = Media::where("media.event_id",$eventId)->where("media.file_type",'document')->get();
            $count_doc = count($documents);

            if($count_doc > 0)
            {
                $data = '';
                for ($i=0; $i < $count_doc; $i++)
                {
                    $extension_array = explode('.', trim($documents[$i]->file_name));
                    $fileType = $extension_array[count($extension_array)-1];
                    $data .= '<div class="col-md-3 col-sm-4 col-xs-6"><a target="_blank" href="'.asset($documents[$i]->file_path).'" >'.$documents[$i]->file_name.'</a><div class="edit"><a href="#" class="removeDocument" docId="'.$documents[$i]->id.'"><i class="fa fa-times fa-2x"></i></a></div></div>';
                }
                $success = 1;
            }
            else
            {
                $data = 'No Documents';
                $success = 0;
            }
        }
        $final_data[] = $success;
        $final_data[] = $data;
        return \Response::json(['success'=>$final_data[0],'data'=>$final_data[1]]);
        //return $final_data;
    }

    public function removeImage(Request $request,$id)
    {
        $Input          = $request->all();
        $eventsID       = $id;
        $imageId        = $Input['imageId'];
        $media          = Media::find($imageId);
        $imagePath      = $media['file_path'];
        $thumbImagePath = $media['thumb_file_path'];
        $media->delete();
        unlink(public_path().$imagePath);
        unlink(public_path().$thumbImagePath);

        // Store in audit
        $auditData                = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'event';
        $auditData['action']      = 'event.imageRemoved';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);

        $result  = 1;
        $message = "success";
        return \Response::json(['success'=>$result,'message'=>$message]);
    }

    public function removeVideo(Request $request,$id)
    {
        $Input          = $request->all();
        $eventsID       = $id;
        $videoId        = $Input['videoId'];
        $media          = Media::find($videoId);
        $videoPath      = $media['file_path'];
        $media->delete();
        unlink(public_path().$videoPath);

        // Store in audit
        $auditData                = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'event';
        $auditData['action']      = 'event.videoRemoved';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);

        $result  = 1;
        $message = "success";
        return \Response::json(['success'=>$result,'message'=>$message]);
    }

    public function removeDocument(Request $request,$id)
    {
        $Input    = $request->all();
        $eventsID = $id;
        $docId    = $Input['docId'];
        $media    = Media::find($docId);
        $docPath  = $media['file_path'];
        $media->delete();

        unlink(public_path().$docPath);

        // Store in audit
        $auditData                = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'event';
        $auditData['action']      = 'event.documentRemoved';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);

        $result  = 1;
        $message = "success";
        return \Response::json(['success'=>$result,'message'=>$message]);
    }

    public function storeTicket(Request $request)
    {
        $this->validate($request, [
            'title'      => 'required',
            'short_desc' => 'required'
        ]);

        $input     = $request->all();
        $ticket_id = $input['id'];

        if($ticket_id)
        {
            //EventTickets::find($ticket_id)->update($request->all());
            unset($input['_token']);
            unset($input['id']);
            $eventTicket = EventTickets::where('id', $ticket_id)->update($input);
            $tktId       = $ticket_id;
            $eventId     = $input['event_id'];
            $action      = 'event.ticketEdit';
        }
        else
        {
            $eventTickets = new EventTickets();
            $eventTickets->event_id        = $input['event_id'];
            $eventTickets->title           = $input['title'];
            $eventTickets->short_desc      = $input['short_desc'];
            $eventTickets->full_desc       = $input['full_desc'];
            $eventTickets->ticket_price    = $input['ticket_price'];
            $eventTickets->ticket_currency = $input['ticket_currency'];
            $eventTickets->status          = $input['status'];
            $eventTickets->created_at      = Carbon::now();
            $eventTickets->updated_at      = Carbon::now();
            $eventTickets->save();

            $tktId       = $eventTickets->getAttribute('id');
            $eventId     = $input['event_id'];
            $action      = 'event.tickeCreate';
        }

        //$eventTicket   = EventTickets::create($input);

        if($request->hasFile('ticket_image'))
        {
            $file = $request->file('ticket_image');

            // Create Directory
            Storage::disk('upload')->makeDirectory('Events/'.$eventId.'/tickets');

            $storage = public_path()."/uploads/Events/".$eventId."/tickets";

            // Create A FileName From Uploaded File.
            $fileName = time().$request->file('ticket_image')->getClientOriginalName();

            // Remove Spaces In The Filename And Convert To LowerCase.
            $fileName = str_replace(' ', '', strtolower($fileName));

            // Move The File To Storage Directory
            if($request->file('ticket_image')->move($storage,$fileName)){
                $FileNameStore          = "/uploads/Events/".$eventId."/tickets/".$fileName;
                $eventTkt               = EventTickets::find($tktId);
                $eventTkt->ticket_image = $FileNameStore;
                $eventTkt->save();
            }
        }

        // Store in audit
        $auditData                = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'event';
        $auditData['action']      = $action;
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);

        return redirect()->back()->with('flash_message','Event Ticket Created Successfully');
    }

    public function ticketDestroy(Request $request)
    {
        $Input         = $request->all();
        $ticketIDs     = $Input['TicketIDs'];
        $ticketIdArray = explode(',', $ticketIDs);
        foreach ($ticketIdArray as $key => $ticketId)
        {
            $eventTicket  = EventTickets::find($ticketId);
            $tktImagePath = $eventTicket['ticket_image'];
            $eventTicket->delete();
            if($tktImagePath)
            {
                unlink(public_path().$tktImagePath);
            }

            // Store in audit
            $auditData                = array();
            $auditData['user_id']     = \Auth::user()->id;
            $auditData['section']     = 'event';
            $auditData['action']      = 'event.ticketDelete';
            $auditData['time_stamp']  = time();
            $auditData['device_id']   = \Request::ip();
            $auditData['device_type'] = 'web';
            auditTrackRecord($auditData);


        }
        return redirect()->back()->with('flash_message','Ticket deleted successfully');
    }

    public function ticketchangeStatus(Request $request, $id)
    {
        $Input      = $request->all();
        $statusMode = $Input['mode'];
        $eventTicket       = EventTickets::find($id);
        if ($statusMode == 0)
        {
            $eventTicket->status = 0;
        }
        else
        {
            $eventTicket->status = 1;
        }
        $eventTicket->save();

        // Store in audit
        $auditData                = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'event';
        $auditData['action']      = 'event.ticketChangeStatus';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);


        return redirect()->back()->with('flash_message','Status changed successfully');
    }

    public function getTicketRecords(Request $request)
    {
        $Input                   = $request->all();
        $ticketID                = $Input['ticketId'];
        $eventTicket             = EventTickets::find($ticketID);
        $data                    = array();
        $data['id']              = $eventTicket->id;
        $data['event_id']        = $eventTicket->event_id;
        $data['title']           = $eventTicket->title;
        $data['short_desc']      = $eventTicket->short_desc;
        $data['full_desc']       = $eventTicket->full_desc;
        $data['ticket_price']    = $eventTicket->ticket_price;
        $data['ticket_currency'] = $eventTicket->ticket_currency;
        $data['ticket_image']    = asset($eventTicket->ticket_image);
        $data['status']          = $eventTicket->status;
        $result                  = 1;

        return \Response::json(['success'=>$result,'data'=>$data]);
    }

}
