<?php
namespace App\Modules\Events\Http\Controllers;


use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Storage;
use Mail;

use App\Modules\Events\Models\User;
use App\Modules\Events\Models\UserEvents;
use App\Modules\Events\Models\Role;
use App\Modules\Events\Models\Meetups;
use App\Modules\Events\Models\MyAgenda;
use App\Modules\Events\Models\SessionsSpeakers;
use App\Modules\Events\Models\Feedback;
use App\Modules\Events\Models\UserRequestForSession;
use App\Modules\Events\Models\UserPollingAnswer;
use App\Modules\Events\Models\RoleFieldValues;
use App\Modules\Events\Models\Question;
use App\Modules\Events\Models\EventTicketsUser;
use App\Modules\Events\Models\FieldsRelation;
use App\Modules\Events\Models\Fields;
use App\Modules\Events\Models\EmailTemplate;
use App\Modules\Events\Models\Configuration;

use Validator;

use DB;

use Hash;

use Yajra\Datatables\Facades\Datatables;


class UserController extends Controller

{


    /**

     * Display a listing of the resource.

     *

     * @return \Illuminate\Http\Response

     */

    public function index(Request $request)
    {
        $data  = User::orderBy('id','DESC');
        $roles = Role::get();

        return view('events::users.index',compact('data','roles'))

            ->with('i', ($request->input('page', 1) - 1) * 5);

    }

    public function UserData(Request $request)
    {
        $provinces = User::select('*');

        return Datatables::of($provinces)->make(true);
    }

    /**

     * Show the form for creating a new resource.

     *

     * @return \Illuminate\Http\Response

     */

    public function create()
    {
        $roles = Role::pluck('display_name','id');

        return view('events::users.create',compact('roles'));
    }

    public function importCreate()
    {
        $roles = Role::pluck('display_name','id')->toarray();
        return view('events::users.import',compact('roles'));
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
            'name'     => 'required',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'roles'    => 'required'
        ]);

        $input = $request->all();
        $input['password'] = Hash::make($input['password']);

        $user = new User();
        $user->name         = $request->name;
        $user->email        = $request->email;
        $user->phone_number = $request->phone_number;
        $user->password     =  Hash::make($request->password);
        $user->save();

        foreach ($request->roles as $key => $value) {
            $user->attachRole($value);
        }

        // Store in audit
        $auditData                = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'user';
        $auditData['action']      = 'user.create';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);

        return redirect()->route('users.index')->with('success','User created successfully');
    }


    /**

     * Display the specified resource.

     *

     * @param  int  $id

     * @return \Illuminate\Http\Response

     */

    public function show($id)

    {

        $user = User::find($id);

        return view('events::users.show',compact('user'));

    }


    /**

     * Show the form for editing the specified resource.

     *

     * @param  int  $id

     * @return \Illuminate\Http\Response

     */

    public function edit($id)

    {

        $user     = User::find($id);

        $roles    = Role::pluck('display_name','id');

        $userRole = $user->roles->pluck('id','id')->toArray();


        return view('events::users.edit',compact('user','roles','userRole'));

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

            'name'     => 'required',

            'email'    => 'required|email|unique:users,email,'.$id,

            'password' => 'same:confirm-password',

            'roles'    => 'required'

        ]);


        $input = $request->all();

        if(!empty($input['password'])){

            $input['password'] = Hash::make($input['password']);

        }else{

            $input = array_except($input,array('password'));

        }


        $user = User::find($id);

        $user->update($input);

        DB::table('role_user')->where('user_id',$id)->delete();

        foreach ($request->input('roles') as $key => $value) {

            $user->attachRole($value);

        }

        // Store in audit
        $auditData = array();
        $auditData['user_id'] = \Auth::user()->id;
        $auditData['section'] = 'user';
        $auditData['action'] = 'user.edit';
        $auditData['time_stamp'] = time();
        $auditData['device_id'] = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);

        return redirect()->route('users.index')->with('success','User updated successfully');
    }


    /**

     * Remove the specified resource from storage.

     *

     * @param  int  $id

     * @return \Illuminate\Http\Response

     */

    public function destroy($id)
    {

        User::find($id)->delete();

        UserEvents::where('user_id',$id)->delete();

        Meetups::where('user_id',$id)->delete();

        Meetups::where('to_user_id',$id)->delete();

        MyAgenda::where('user_id',$id)->delete();

        SessionsSpeakers::where('speaker_id', $id)->delete();

        Feedback::where('user_id',$id)->delete();

        UserRequestForSession::where('user_id',$id)->delete();

        UserPollingAnswer::where('user_id',$id)->delete();

        DB::table('role_user')->where('user_id',$id)->delete();

        RoleFieldValues::where('user_id',$id)->delete();

        Question::where('user_id',$id)->delete();

        EventTicketsUser::where('user_id',$id)->delete();

        // Store in audit
        $auditData = array();
        $auditData['user_id'] = \Auth::user()->id;
        $auditData['section'] = 'user';
        $auditData['action'] = 'user.delete';
        $auditData['time_stamp'] = time();
        $auditData['device_id'] = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);

        return redirect()->route('users.index')->with('success','User deleted successfully');
    }





    public function uplodCSV(Request $request)
    {
        $roleId = $request->role_id;

        $FieldIds = FieldsRelation::where('role_id',$roleId)->pluck('field_id');


        if($request->hasFile('import') && $roleId != '')
        {
            if($request->file('import')->isValid() && $request->file('import')->getClientOriginalExtension() != 'csv')
            {
                $res['success'] = '0';
                $res['message'] = 'Only CSV file allowed, Please upload CSV file.';
                return json_encode($res);
            }


            $file = $request->file('import');

            // Create Directory
            Storage::disk('upload')->makeDirectory('CSV');

            $storage = public_path()."/uploads/CSV";

            // Create A FileName From Uploaded File.
            $fileName = 'users.csv';

            // Remove Spaces In The Filename And Convert To LowerCase.
            $fileName = str_replace(' ', '', strtolower($fileName));

            // Move The File To Storage Directory
            if($request->file('import')->move($storage,$fileName))
            {
                $delimiter = ';';
                $enclosure = '"';
                $line     = 1;
                $headers  = array();
                $handle        = fopen($storage .'/'. $fileName, "r");

                $setheader     = array();
                $newheaders    = array();
                $diffrenColumn = array();
                $gmsg          = '';
                $errorsno      = 0;

                while(($data = fgetcsv($handle, 0, $delimiter, $enclosure)) !== false)
                {
                    if($line==1)
                    {
                        foreach ($data as $key => $name)
                        {
                            $headers[$key] = trim(strtolower($name));
                            $datas[] = trim(strtolower($name));
                        }

                        $dropDownHeader='<option value="0">--Select--</option>';
                        foreach ($data as $key => $name)
                        {
                            if (isset($headers[$key]))
                            {
                                $rawdata[$headers[$key]] = $name;
                                $dropDownHeader.='<option value="'.$headers[$key].'">'.$headers[$key].'</option>';
                            }
                        }

                        $diffrenColumn = json_encode($diffrenColumn);
                        $res['success'] = '1';
                        $res['headerOptions'] = $dropDownHeader;
                        if($FieldIds)
                        {
                            $html = '';
                            foreach ($FieldIds as $key => $FieldId)
                            {
                                $FieldDetail = Fields::find($FieldId);
                                $html .= '<div class="col-xs-6 col-sm-6 col-md-6">
                                            <div class="form-group">
                                                <strong>'.$FieldDetail->display_name.'</strong>
                                            </div>
                                        </div>

                                        <div class="col-xs-6 col-sm-6 col-md-6">
                                            <div class="form-group">
                                                <select class="form-control" id="field_'.$FieldDetail->id.'" name="field_'.$FieldDetail->id.'">'.$dropDownHeader.'</select>
                                            </div>
                                        </div>';

                            }
                        }
                        if($html)
                        {
                            $res['dynaForm'] = $html;
                        }
                        else
                        {
                            $res['dynaForm'] = '';
                        }


                        return json_encode($res);

                    }
                }
            }
            else
            {
                $res['success'] = '0';
                $res['message'] = 'Error in file upload';
                return json_encode($res);
            }
        }
        else
        {
            $res['success'] = '0';
            $res['message'] = 'Please select csv file OR Select user role.';
            return json_encode($res);
        }
    }

    public function mapUserStore(Request $request)
    {

        $datas = $request->all();
        unset($datas['_token']);
        unset($datas['roleId']);

        /*$datas['name']              =$request->name;
        $datas['email']             =$request->email;
        $datas['phone_number']      =$request->phone_number;*/


        $roleId = $request->roleId;

        $delimiter     = ';';
        $enclosure     = '"';
        $line          = 1;
        // Create Directory
        Storage::disk('upload')->makeDirectory('CSV');
        $storage       = public_path()."/uploads/CSV";

        // Create A FileName From Uploaded File.
        $fileName      = 'users.csv';

        // Remove Spaces In The Filename And Convert To LowerCase.
        $fileName      = str_replace(' ', '', strtolower($fileName));

        $headers       = array();
        $handle        = fopen($storage .'/'. $fileName, "r");

        $setheader     = array();
        $newheaders    = array();
        $diffrenColumn = array();
        $gmsg          = '';
        $errorsno      = 0;

        while(($data = fgetcsv($handle, 0, $delimiter, $enclosure)) !== false)
        {
            if($line==1)
            {
                foreach ($data as $key => $name)
                {
                    foreach ($datas as $newkey => $value)
                    {
                        if($value == strtolower($name))
                        {
                            $newHeader[] = strtolower($newkey);
                        }
                    }
                    $headers[$key] = strtolower($name);
                }

            }
            else
            {

                $rawdata = array();
                $newarr = $newHeader;
                $data_error=0;
                $data_msg=array();

                foreach ($data as $key => $value)
                {
                    if(count($newarr) > 0)
                    {
                        if($key > count($newarr))
                        {
                            $extVal = count($data)-count($newarr);

                            if($newarr[$key-$extVal] == 'name' && $value == '')
                            {
                                $data_error=1;
                                $data_msg[]="Name is required";
                            }

                            if($newarr[$key-$extVal] == 'email' && $value == '')
                            {
                                $data_error=1;
                                $data_msg[]="Email is required";
                            }
                        }
                        else
                        {
                            if($newarr[$key] == 'name' && $value == '')
                            {
                                $data_error=1;
                                $data_msg[]="Name is required";
                            }


                            if($newarr[$key] == 'email' && $value == '')
                            {
                                $data_error=1;
                                $data_msg[]="Email is required";
                            }
                        }
                    }

                    /*if($value == '')
                    {
                        if($newarr[$key] != 'acct_name')
                        {
                            unset($data[$key]);
                            unset($newarr[$key]);
                        }
                    }*/

                    if($key > count($newarr))
                    {
                        $extVal = count($data)-count($newarr);
                        if(!in_array($headers[$key-$extVal], $datas))
                        {
                            unset($data[$key]);
                        }
                    }
                    else
                    {
                        if(!in_array($headers[$key], $datas))
                        {
                            unset($data[$key]);
                        }
                    }
                }

                $data = array_values($data);
                $updatedheaders = array_values($newarr);


                foreach ($data as $key => $name)
                {
                    $rawdata[$updatedheaders[$key]] = $name;
                }

                $emailRules = array('email' => 'unique:users,email');
                $phoneRules = array('phone_number' => 'unique:users,phone_number');

                $emailValidator = Validator::make($rawdata, $emailRules);
                $phoneValidator = Validator::make($rawdata, $phoneRules);

                if ($emailValidator->fails())
                {
                    $data_error=1;
                    $data_msg[]="Email Already Exist.";
                }

                if ($phoneValidator->fails())
                {
                    $data_error=1;
                    $data_msg[]="Phone Number Already Exist.";
                }



                /*// store data in transaction mapped Table.
                foreach ($datas as $datakey => $datavalue)
                {
                    $str[$datakey] =$datavalue;
                }

                $cont = implode("||", $headers);
                $str['checkheader'] = $cont;
                $chkresult = $this->MysqlSchema->table('customer_maped')->where('checkheader',$cont)->get();

                if(!$chkresult)
                {
                    $result = $this->MysqlSchema->table('customer_maped')->insert($str);
                }*/

                if($data_error == 1)
                {
                    $errorsno++;
                    $gmsg .= "Error On Row Number: "." ".$line." : ".implode(", ",$data_msg).".<br/><br/>";
                    $line++;
                    continue;
                }

                // Importing Invoice from CSV file
                $this->storeUserMappData($rawdata, $roleId);
                unset($data);
            }
            $line++;
        }

        return redirect()->route('users.index')->with('success','User imported successfully');
    }

    public function storeUserMappData($rawdatas, $roleId)
    {
        $fieldKeys = preg_grep('/^field_/', array_keys($rawdatas));
        if(isset($fieldKeys))
        {
            foreach ($fieldKeys as $key => $value)
            {
                $fieldsArr[$value] = $rawdatas[$value];
                unset($rawdatas[$value]);
            }
        }

        //$name = strtoupper(substr($rawdatas['name'],0,4));
        //$number = substr($rawdatas['phone_number'],0,4);

        $name = strtoupper($rawdatas['name']);
        $number = '123';
        $password = $name.$number;
        $rawdatas['password']     = Hash::make($password);

        $user = User::create($rawdatas);

        $data = array(
                'user_id' => $user['id'],
                'role_id' => $roleId
            );

        // Insert user role
        DB::connection()->table('role_user')->insert($data);

        // Insert user custom fields data...
        if(isset($fieldsArr))
        {
            foreach ($fieldsArr as $fkey => $fieldsVal)
            {
                $fidArr = explode("field_",$fkey);
                $fid = $fidArr[1];

                $insert_data = array(
                    'user_id'    => $user['id'],
                    'role_id'    => $roleId,
                    'field_id'   => $fid,
                    'value'      => $fieldsVal,
                    'is_privacy' => 0
                );

                RoleFieldValues::create($insert_data);
            }
        }


        // Send mail code started here...
        $roleData = Role::find($roleId);
        $event_name = "NSIT Mahatma mandir";
        $emailTemplateDetail =  EmailTemplate::where('type','register')->where('status',1)->first();

        $subjectContent   = $emailTemplateDetail->subject;
        $content = $emailTemplateDetail->message;

        $link_ios = Configuration::select('value')->where('name','ios_app_link')->first();
        $link_ios = json_decode($link_ios);
        $link_android = Configuration::select('value')->where('name','android_app_link')->first();
        $link_android = json_decode($link_android);

        // Replace pattern in content Data.
        $content = str_replace("{event_name}", $event_name, $content);//replace event Name
        $content = str_replace("{first_name}", $user['name'], $content);//replace first Name
        $content = str_replace("{role_name}", $roleData->display_name, $content);//replace role name.
        $content = str_replace("{user_email}", $rawdatas['email'], $content);//replace role name.
        $content = str_replace("{user_password}", $password, $content);//replace role name.
        $content = str_replace("{link_ios}", $link_ios->value, $content);//replace link ios
        $content = str_replace("{link_android}", $link_android->value, $content);//replace link android



        // Replace pattern in subject.
        $subjectContent = str_replace("{event_name}", $event_name, $subjectContent);//replace event Name
        $subjectContent = str_replace("{first_name}", $user['name'], $subjectContent);//replace first Name
        $subjectContent = str_replace("{role_name}", $roleData->display_name, $subjectContent);//replace role name.
        $subjectContent = str_replace("{user_email}", $rawdatas['email'], $subjectContent);//replace role name.
        $subjectContent = str_replace("{user_password}", $password, $subjectContent);//replace role name.
        $subjectContent = str_replace("{link_ios}", $link_ios->value, $subjectContent);//replace link ios
        $subjectContent = str_replace("{link_android}", $link_android->value, $subjectContent);//replace link android

        $emailData['content'] = $content;
        $emailData['subject'] = $subjectContent;
        $emailData['email']   = $rawdatas['email'];

        Mail::send(['html' => 'email_template.welcome'], $emailData, function($message) use ($emailData)
        {
            $message->from('no-reply@site.com', "Indolytics");
            $message->subject($emailData['subject']);
            $message->to($emailData['email']);
        });
        // Send mail code ended here...

        return;

    }

}