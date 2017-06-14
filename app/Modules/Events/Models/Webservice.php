<?php

/*namespace App\Modules\Event\Models;

use Dingo\Api\Routing\Helpers;
use Illuminate\Database\Eloquent\Model;
use DB;
use \stdClass;
use Mail;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;*/


namespace  App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Http\Request;

use App\Models\Projects;
use Config;
use Illuminate\Support\Facades\Session;

class Webservice extends Model
{
    protected $hadoop_db_conn;
    protected $connection;
    public $JsonArray   = array();

    public function __construct()
    {
        $Data = json_decode(stripslashes(htmlspecialchars_decode(urldecode(\Request::input('reqObject')))));
        $TaskData = $Data->taskData;
        $project_conn_secret = $TaskData->secret_token;
        if($project_conn_secret != '')
        {
            $data = Projects::where('secret',trim($project_conn_secret))->first();
            if(count($data))
            {
                $schemaName = $data->db;
                $secret = $data->db_user;
                $password = \Crypt::decrypt($data->db_password);

                Config::set('database.connections.'.$schemaName, array(
                    'driver'    => 'mysql',
                    'host'      => 'localhost',
                    'database'  => $schemaName,
                    'username'  => $secret,
                    'password'  => $password,
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix'    => '',
                ));
                $project_conn = 'project_conn'.$data->id;
                Session::set('project_conn_id',$data->id);
                Session::set($project_conn,$schemaName);
                $this->connection = $schemaName;
                $this->hadoop_db_conn = $schemaName;
            }
            else
            {
                $this->JsonArray['data'] = '';
                $this->JsonArray['message'] = "Wrong Connection for Application please check your Secret Token.";
                $this->JsonArray['status_code'] = 204;
                header("content-type: application/json");
                echo json_encode($this->JsonArray);exit;
            }

        }
        else
        {
            $this->JsonArray['data'] = '';
            $this->JsonArray['message'] = "Wrong Connection for Application please check your Secret Token.";
            $this->JsonArray['status_code'] = 204;
            header("content-type: application/json");
            echo json_encode($this->JsonArray);exit;
        }
    }

    /**
     * Use To Fetch Application (global) config..
     *
     * Request Object :
     * {"task":"appConfig","taskData":{}}
     */
    public function appConfig($TaskData){

        $configurations = Configuration::all()->toArray();

        if (empty($configurations)) {
            $configurations = array();

            return $this->response->array([
                'data'        => $configurations,
                'status_code' => 204
            ]);
        }

        return $this->response->array([
            'data'        => $configurations,
            'status_code' => 200
        ]);
    }

    /**
     * Use To Login User.
     *
     * Request Object :
     * {"task":"login","taskData":{"email":"testing.tasol@gmail.com","password":"123456","phone_number":"","device_type":"Android/ios","device_id":"5435435435","device_token":"forpush","event_id":"","is_google":"","is_facebook":""}}
     */
    public function login($TaskData){

        if (isset($TaskData->phone_number) && !empty($TaskData->phone_number)){
            $userDetail =  User::where('phone_number',$TaskData->phone_number) -> first();
            if (empty($userDetail)){
                return $this->response->array([
                    'message'     => 'Mobile Number Not Exist.',
                    'status_code' => 404
                ]);
            }
            $loggedInUser = Auth::loginUsingId($userDetail['id']);
            $token        = JWTAuth::fromUser($loggedInUser);
        }else{

            $credentials = array('email' => $TaskData->email, 'password' => $TaskData->password);

            try {

                if ($TaskData->is_google) {
                    // Set google id
                    User::where('email',$TaskData->email)->update((array('google_id' => $TaskData->password,'reg_type' => "google")));
                }

                if ($TaskData->is_facebook) {
                    // Set facebook id
                    User::where('email',$TaskData->email)->update((array('fb_id' => $TaskData->password,'reg_type' => "facebook")));
                }

                $user_detail =  User::where('email',$TaskData->email)->first();

                if (isset($user_detail)) {

                    if (($user_detail['fb_id'] == $TaskData->password) || ($user_detail['google_id'] == $TaskData->password)) {
                        $loggedInUser = Auth::loginUsingId($user_detail['id']);
                        $token        = JWTAuth::fromUser($loggedInUser);
                        $loggedInUser = Auth::user();

                        // if already same device token inserted in database then it's update blank
                        User::where('device_token', '=', $TaskData->device_token)->update((array('device_token' => "")));

                        // Update latest device token for particular login user
                        User::where('id', '=', $loggedInUser->id)->update((array('device_token' => $TaskData->device_token)));

                        $loggedInUser['device_id']    = $TaskData->device_id;
                        $loggedInUser['device_type']  = $TaskData->device_type;
                        $loggedInUser['device_token'] = $TaskData->device_token;

                        $loggedInUser->save();

                        //User Data
                        $user_data = array(
                            'id'           => $loggedInUser->id,
                            'name'         => $loggedInUser->name,
                            'email'        => $loggedInUser->email,
                            'phone_number' => $loggedInUser->phone_number,
                            'user_avatar'  => ($loggedInUser->user_avatar) ? url('/').$loggedInUser->user_avatar : "",
                            );

                        //User registered and event data
                        $event_data = $this->userRequestForEvent($loggedInUser->id,$TaskData->event_id);

                        return $this->response->array([
                            'message'     => $this->messages['auth']['valid'],
                            'user_id'     => $loggedInUser->id,
                            'user_data'   => $user_data,
                            'event_data'  => $event_data['event_data'],
                            'status_code' => 200,
                            'token'       => $token
                        ]);
                    }
                }

                // attempt to verify the credentials and create a token for the user
                if (!$token = JWTAuth::attempt($credentials)) {
                    return $this->response->array([
                        'message'     => $this->messages['auth']['invalid'],
                        'status_code' => 401
                    ]);
                }
            } catch (JWTException $e) {
                throw new HttpException(500);
                // something went wrong whilst attempting to encode the token
                return $this->response->array([
                        'message'     => 'Could not create token.',
                        'status_code' => 500
                    ]);
            }

            $loggedInUser = Auth::user();
        }

        // if already same device token inserted in database then it's update blank
        User::where('device_token', '=', $TaskData->device_token)->update((array('device_token' => "")));

        // Update latest device token for particular login user
        User::where('id', '=', $loggedInUser->id)->update((array('device_token' => $TaskData->device_token)));

        $loggedInUser['device_id']    = $TaskData->device_id;
        $loggedInUser['device_type']  = $TaskData->device_type;
        $loggedInUser['device_token'] = $TaskData->device_token;

        $loggedInUser->save();

        //User Data
        $user_data = array(
            'id'           => $loggedInUser->id,
            'name'         => $loggedInUser->name,
            'email'        => $loggedInUser->email,
            'phone_number' => $loggedInUser->phone_number,
            'user_avatar'  => ($loggedInUser->user_avatar) ? url('/').$loggedInUser->user_avatar : "",
            );

        //User registered and event data
        $event_data = $this->userRequestForEvent($loggedInUser->id,$TaskData->event_id);


        return $this->response->array([
            'message'     => $this->messages['auth']['valid'],
            'user_id'     => $loggedInUser->id,
            'user_data'   => $user_data,
            'event_data'  => $event_data['event_data'],
            'status_code' => 200,
            'token'       => $token
        ]);
    }

    /**
     * Use To Logout User.
     *
     * Request Object :
     * {"task":"logout","taskData":{}}
     */
    public function logout($TaskData) {
        Auth::logout();

        return $this->response->array([
            'message'     => "You have successfully logout.",
            'status_code' => 200
        ]);
    }
    /**
     * Use To Register User.
     *
     * Request Object :
     * {"task":"register","taskData":{"name":"irshad","email":"irshad@tasolglobal.com","password":"123456","phone_number":"9898900885","device_id":"device id", "device_type":"device type", "device_token":"forpush", "imageUrl":"http://img.fifa.com/mm/photo/ballon-dor/playeroftheyear-men/02/86/29/08/2862908_full-prt.jpg" , "is_facebook":"1/0", "is_google":"1/0","event_id":""}}
     *
     */
    public function register($TaskData, $userAvatar){

        $input                 = array();
        $input['name']         = $TaskData->name;
        $input['email']        = $TaskData->email;
        $input['password']     = Hash::make($TaskData->password);
        $input['phone_number'] = $TaskData->phone_number;
        $input['device_id']    = $TaskData->device_type;
        $input['device_type']  = $TaskData->device_type;
        $input['device_token'] = $TaskData->device_token;
        $input['user_avatar']  = "";
        $FileNameStore         = "";

        $emailRules = array('email' => 'unique:users,email');
        $phoneRules = array('phone_number' => 'unique:users,phone_number');

        $emailValidator = Validator::make($input, $emailRules);
        $phoneValidator = Validator::make($input, $phoneRules);
        $token = "";

        if ($emailValidator->fails()) {
            return $this->response->array([
                'message'     => 'Email Already Exist.',
                'status_code' => 702
            ]);
        }elseif ($phoneValidator->fails()){
            return $this->response->array([
                'message'     => 'Phone Number Already Exist.',
                'status_code' => 702
            ]);
        }

        if (!empty($userAvatar)){

            if(substr($userAvatar->getMimeType(), 0, 5) != 'image') {
                return $this->response->array([
                    'message'     => 'Please upload only jpeg,jpg and png image.',
                    'status_code' => 702
                ]);
            }

            if (!Storage::disk('upload')->exists('user_avatar')){
                // Create Directory
                Storage::disk('upload')->makeDirectory('user_avatar');
            }

            $storage = public_path()."/uploads/user_avatar";

            // Create A FileName From Uploaded File.
            $fileName = time().$userAvatar->getClientOriginalName();

            // Remove Spaces In The Filename And Convert To LowerCase.
            $fileName = str_replace(' ', '', strtolower($fileName));

            // Move The File To Storage Directory
            if($userAvatar->move($storage,$fileName)){
                $FileNameStore      = "/uploads/user_avatar/".$fileName;
            }

            $input['user_avatar'] = $FileNameStore;

        }elseif (isset($TaskData->imageUrl) && !empty($TaskData->imageUrl)){

            if (Storage::disk('upload')->exists('user_avatar')){
                // Create Directory
                Storage::disk('upload')->makeDirectory('user_avatar');
            }

            $path          = $TaskData->imageUrl;
            $imageName     = time().basename($path);
            $imageName     = str_replace(' ', '', strtolower($imageName));
            $imageName     = explode('?', $imageName);
            $image         = file_get_contents($path);
            $FileNameStore = "/uploads/user_avatar/".$imageName[0];

            file_put_contents(public_path().$FileNameStore, $image);
            $input['user_avatar'] = $FileNameStore;
        }

        if ($TaskData->is_facebook == 1) {
            $input['reg_type'] = "facebook";
            $input['fb_id']    = $TaskData->password;
        }
        elseif ($TaskData->is_google == 1) {
           $input['reg_type']  = "google";
           $input['google_id'] = $TaskData->password;
        }

        if (!empty($TaskData->device_token)){
            // if already same device token inserted in database then it's update blank
            User::where('device_token', '=', $TaskData->device_token)->update((array('device_token' => "")));
        }

        $user = User::create($input);

        $role_id = Role::where('name', '=', 'visitor')->first();

        $data = array(
                'user_id' => $user['id'],
                'role_id' => $role_id->id
            );

        // Insert user role
        DB::connection()->table('role_user')->insert($data);

        $event_data = array();
        if ($TaskData->event_id > 0) {
            //User registered and event data
            $event_data = $this->userRequestForEvent($user['id'], $TaskData->event_id);
            $event_data = $event_data['event_data'];
        }

        $loggedInUser = Auth::loginUsingId($user['id']);
        $token        = JWTAuth::fromUser($loggedInUser);

        if ($TaskData->is_facebook == 1 || $TaskData->is_google == 1) {
            $credentials = array('email' => $TaskData->email, 'password' => $TaskData->password);

            try {
                // attempt to verify the credentials and create a token for the user
                if (!$token = JWTAuth::attempt($credentials)) {
                    return $this->response->error($this->messages['auth']['invalid'],401);
                    throw new AccessDeniedHttpException();
                }
            } catch (JWTException $e) {
                throw new HttpException(500);
                // something went wrong whilst attempting to encode the token
                return $this->response->error('could_not_create_token',500);
            }
        }

        $userDetail  =  User::findOrFail($user['id']);

        // Send mail code started here...
        $emailTemplateDetail =  EmailTemplate::where('type','register')->where('status',1)->first();

        $subjectContent   = $emailTemplateDetail->subject;
        $content = $emailTemplateDetail->message;

        $link = "http://tasolglobal.com";
        // Replace pattern in content Data.
        $content = str_replace("{event_name}", $event_data[0]['name'], $content);//replace event Name
        $content = str_replace("{first_name}", $userDetail->name, $content);//replace first Name
        $content = str_replace("{link}", $link, $content);//replace link
        $content = str_replace("{role_name}", $role_id->display_name, $content);//replace role name.


        // Replace pattern in subject.
        $subjectContent = str_replace("{event_name}", $event_data[0]['name'], $subjectContent);//replace event Name
        $subjectContent = str_replace("{first_name}", $userDetail->name, $subjectContent);//replace first Name
        $subjectContent = str_replace("{link}", $link, $subjectContent);//replace link
        $subjectContent = str_replace("{role_name}", $role_id->display_name, $subjectContent);//replace role name.

        $input['content'] = $content;
        $input['subject'] = $subjectContent;

        Mail::send(['html' => 'email_template.welcome'], $input, function($message) use ($input)
        {
            $message->from('no-reply@site.com', "Indolytics");
            $message->subject($input['subject']);
            $message->to($input['email']);
        });
        // Send mail code ended here...






        $user_data = array(
            'id'           => $userDetail->id,
            'name'         => $userDetail->name,
            'email'        => $userDetail->email,
            'phone_number' => $userDetail->phone_number,
            'user_avatar'  => ($userDetail->user_avatar) ? url('/').$userDetail->user_avatar : "",
            );

        return $this->response->array([
            'message'     => 'User Created Successfully.',
            'user_id'     => $loggedInUser->id,
            'user_data'   => $user_data,
            'event_data'  => $event_data,
            'status_code' => 200,
            'token'       => $token
        ]);
    }

    /**
     * Use To Forgot user password.
     *
     * Request Object :
     * {"task":"getMenu","taskData":{"type":"xhdpi/2X,3X"}}
     *
     */
    public function getMenu($TaskData){
        // Get menus data
        $menus_data = Menu::get();
        $data = array();
        foreach ($menus_data as $key => $menu) {
            if ($menu['status'] == 1) {
                $icon = "";
                if ($TaskData->type) {
                    $icon = asset("/uploads/Menus/".$menu['id']."/".$TaskData->type."_".$menu['icon']);
                }
                $menu_data = array(
                    "id"             => strval($menu['id']),
                    "parent_id"      => strval($menu['parent']),
                    "title"          => $menu['title'],
                    "descriptions"   => $menu['descriptions'],
                    "view_name"      => $menu['view_name'],
                    "default_page"   => strval($menu['default_page']),
                    "order_no"       => strval($menu['order']),
                    'icon'           => $icon,
                    "login_required" => strval($menu['login_requuired']),
                    "screens"        => $menu['screens'],
                    "published"      => strval($menu['status']),
                    "extra_data"     => array(
                                            "extra_data"  => "",
                                            "cmsPage"     => "",
                                            "webLink"     => "",
                                            "HTMLContent" => ""
                                            ),
                    "display_icon"   => strval($menu['display_icon']),
                    "category_id"    => ""
                    );
                if ($menu['extra_data']) {
                    $cms_page_content = Cmspage::select('description')->where('id', $menu['cmsPage'])->first();

                    $menu_data['extra_data'] = array(
                        "cms_page_content" => isset($cms_page_content['description']) ? $cms_page_content['description'] : "",
                        "web_link"         => $menu['webLink'],
                        "html_content"     => $menu['HTMLContent']
                        );
                }
                if ($menu['view_name'] == "categoryblog") {

                    $menu_data['category_id'] = strval($menu['cat_id']);
                }
                $data[] = $menu_data;
            }
        }
        if (empty($data)) {
                return $this->response->array([
                'data'        => $data,
                'message'     => "No menu created yet",
                'status_code' => 204
            ]);
        }
        return $this->response->array([
            'data'        => $data,
            'message'     => "",
            'status_code' => 200
        ]);
    }
    /**
     * Use To Forgot user password.
     *
     * Request Object :
     * {"task":"forgotPassword","taskData":{"email":"irshad@tasolglobal.com","phone_number":""}}
     *
     */
    public function forgotPassword($TaskData){
        if ($TaskData->email) {
            $request = array();
            $request['email'] = $TaskData->email;

            $validator = Validator::make($request, [
                'email' => 'unique:users,email'
            ]);

            if (!$validator->fails()) {
                return $this->response->array([
                'message'     => "Please enter proper email address.",
                'status_code' => 401
                ]);
            }

            $request = new Request;
            $request['email'] = $TaskData->email;

            $this->postEmail($request);

            return $this->response->array([
                'message'     => "We have e-mailed your password reset link!",
                'status_code' => 200
            ]);
        }
        elseif($TaskData->phone_number){
            $user = User::where('phone_number', '=', $TaskData->phone_number)->first();
            if ($user === null) {
               // user doesn't exist
                return $this->response->array([
                'message'     => "Please enter proper mobile number.",
                'status_code' => 401
                ]);
            }

             return $this->response->array([
                'message'     => "Mobile number exists",
                'status_code' => 200
            ]);
        }
    }

    /**
     * Get User Info
     *
     * Request Object :
     * {"task":"userInfo","taskData":{"user_id":"1"}}
     *
     */
    public function userInfo($TaskData){
        $userDetail  =  User::findOrFail($TaskData->user_id);

        $user_data = array(
            'id'           => $userDetail->id,
            'name'         => $userDetail->name,
            'email'        => $userDetail->email,
            'phone_number' => $userDetail->phone_number,
            'user_avatar'  => ($userDetail->user_avatar) ? url('/').$userDetail->user_avatar : "",
            );

        // Get fields data
        $fields_data = Fields::orderBy('id','DESC')->get();

        foreach ($fields_data as $key => $field) {
            $fields_data = array(
                'field_id'        => $field->id,
                'display_name'    => $field->display_name,
                'field_name'      => $field->field_name,
                'value'           => "",
                'field_type'      => $field->field_type,
                'items'           => $field->items,
                'default'         => $field->default,
                'validation_rule' => $field->validation_rule,
                'multiple'        => $field->multiple,
                'active'          => $field->active,
                'required'        => $field->required,
                );
            $fields[] = $fields_data;
        }
        $user_data['fields'] = $fields;

        return $this->response->array([
            'data'        => $user_data,
            'message'     => "",
            'status_code' => 200
        ]);
    }

    /**
     * User Edit
     *
     * Request Object :
     * Get user field data
     * {"task":"userEdit","taskData":{"get_form":"1","user_id":"1","role_id":""}}
     *
     * Update user data
     * {"task":"userEdit","taskData":{"get_form":"","user_id":"1","role_id":"4","name":"Dwarkesh","form_fields":[{"field_id":"3","value":"25","is_privacy":"1"},{"field_id":"2","value":"female","is_privacy":"1"}]}}
     *
     * Note : image key for send user avatar
     */
    public function userEdit($TaskData, $userAvatar){
        // Get user data
        if ($TaskData->get_form == 1) {
            $user      = User::find($TaskData->user_id);
            $roles     = Role::lists('display_name','id');
            $userRoles = $user->roles->lists('id','id')->toArray();

            $data = array(
                'id'             => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'phone_number'   => $user->phone_number,
                'user_avatar'    => ($user->user_avatar) ? url('/').$user->user_avatar : "",
                'user_role_list' => array(),
                //'user_fields'    => array(),
                'role_fields'    => array(),
                'form_fields'    => array()
                );

            /*$user_fields = array(
                array(
                    'caption' => 'Name',
                    'name'    => 'name',
                    'type'    => 'text',
                    'value'   => $user->name
                    ),
                array(
                    'caption' => 'Email',
                    'name'    => 'email',
                    'type'    => 'text',
                    'value'   => $user->email
                    ),
                array(
                    'caption' => 'Phone Number',
                    'name'    => 'phone_number',
                    'type'    => 'numeric',
                    'value'   => $user->phone_number
                ),
                array(
                    'title'   => "Avatar",
                    'name'    => 'user_avatar',
                    'type'    => 'file',
                    'value'   => ($user->user_avatar) ? url('/').$user->user_avatar : ""
                )
            );

            $data['user_fields'] = $user_fields;*/

            // Register fields for all user
            $register_field_data = Fields::where('type', 'register')->get();
            $is_data = "";

            if (!empty($register_field_data[0])) {
                foreach ($register_field_data as $key => $register_field) {
                    if ($register_field->active == 1) {
                        $value      = RoleFieldValues::select('value', 'is_privacy')->where('field_id', $register_field->id)->where('user_id', $TaskData->user_id)->where('role_id', $TaskData->role_id)->first();
                        $group_name = Fields::select('display_name')->where('id', $register_field->group_id)->get();

                        $register_field_data = array(
                            'group_name'       => $group_name[0]['display_name'],
                            'field_id'         => $register_field->id,
                            'role_id'          => isset($register_field->role_id) ? $register_field->role_id : "",
                            'caption'          => $register_field->display_name,
                            'name'             => $register_field->field_name,
                            'hint'             => "",
                            'type'             => $register_field->field_type,
                            'inputtype'        => "",
                            "order_no"         => "",
                            'value'            => isset($value['value']) ? $value['value'] : "",
                            'options'          => array(),
                            'default_value'    => ($register_field->default) ? $register_field->default : "",
                            'validation_rule'  => ($register_field->validation_rule) ? $register_field->validation_rule : "",
                            'is_multiple'      => $register_field->multiple,
                            'is_active'        => $register_field->active,
                            'is_required'      => $register_field->required,
                            'is_searchable'    => $register_field->searchable,
                            'is_filterable'    => $register_field->filterable,
                            'is_privacy'       => array(),
                            'is_privacy_value' => isset($value['is_privacy']) ? $value['is_privacy'] : ""
                            );

                        if ($register_field->items) {
                            $options     = explode(',', $register_field->items);
                            $option_data = array();
                            foreach ($options as $key => $option) {
                                $register_field_option = array(
                                    'text'  => $option,
                                    'value' => $option
                                    );
                                $option_data[] = $register_field_option;
                            }
                            $register_field_data['options'] = $option_data;
                        }

                        if ($register_field->privacy) {
                            $option_data = array();
                            $option_data[0]['text']  = "Public";
                            $option_data[0]['value'] = 0;
                            $option_data[1]['text']  = "Private";
                            $option_data[1]['value'] = 1;

                            $register_field_data['is_privacy'] = $option_data;
                        }

                        $register_fields[] = $register_field_data;
                    }
                    $data['form_fields'] = $register_fields;
                }
            }

            // Get fields data
            $fields_data = Fields::join('fields_relation', 'fields.id', '=', 'fields_relation.field_id')->where('role_id', $TaskData->role_id)->get();

            if (!empty($fields_data[0])) {
                foreach ($fields_data as $key => $field) {
                    if ($field->active == 1 ) {
                        $value      = RoleFieldValues::select('value', 'is_privacy')->where('field_id', $field->field_id)->where('user_id', $TaskData->user_id)->where('role_id', $TaskData->role_id)->first();
                        $group_name = Fields::select('display_name')->where('id', $field->group_id)->get();

                        $fields_data = array(
                            'group_name'       => $group_name[0]['display_name'],
                            'field_id'         => $field->field_id,
                            'role_id'          => isset($field->role_id) ? $field->role_id : "",
                            'caption'          => $field->display_name,
                            'name'             => $field->field_name,
                            'hint'             => "",
                            'type'             => $field->field_type,
                            'inputtype'        => "",
                            "order_no"         => "",
                            'value'            => isset($value['value']) ? $value['value'] : "" ,
                            'options'          => array(),
                            'default_value'    => $field->default,
                            'validation_rule'  => $field->validation_rule,
                            'is_multiple'      => $field->multiple,
                            'is_active'        => $field->active,
                            'is_required'      => $field->required,
                            'is_searchable'    => $register_field->searchable,
                            'is_filterable'    => $register_field->filterable,
                            'is_privacy'       => array(),
                            'is_privacy_value' => isset($value['is_privacy']) ? $value['is_privacy'] : ""
                            );

                        if ($field->items) {
                            $options     = explode(',', $field->items);
                            $option_data = array();
                            foreach ($options as $key => $option) {
                                $fields_option = array(
                                    'text'  => $option,
                                    'value' => $option
                                    );
                                $option_data[] = $fields_option;
                            }
                            $fields_data['options'] = $option_data;
                        }

                        if ($register_field->privacy) {
                            $option_data = array();
                            $option_data[0]['text']  = "Public";
                            $option_data[0]['value'] = 0;
                            $option_data[1]['text']  = "Private";
                            $option_data[1]['value'] = 1;

                            $fields_data['is_privacy'] = $option_data;
                        }
                    $fields[] = $fields_data;
                    }
                }
                $data['form_fields'] = array_merge($data['form_fields'],$fields);
                //$data['form_fields'] = $fields;
            }

            if (empty($register_field_data) && empty($fields_data[0])) {
                return $this->response->array([
                    'message'     => "No fields data found",
                    'status_code' => 204
                ]);
            }

            foreach ($roles as $key => $role) {
                foreach ($userRoles as $user_role) {
                    if($user_role == $key) {
                        $roles = array(
                            'caption' => $role,
                            'value'   => $key
                            );
                        $data['user_role_list'][] = $roles;
                    }
                }
            }

            return $this->response->array([
                'data'        => $data,
                'message'     => "",
                'status_code' => 200
            ]);
        }

        // Update user data
        foreach ($TaskData->form_fields as $key => $form_field) {

            $insert_data = array(
                'user_id'    => $TaskData->user_id,
                'role_id'    => $TaskData->role_id,
                'field_id'   => $form_field->field_id,
                'value'      => $form_field->value,
                'is_privacy' => $form_field->is_privacy
            );

            $role_field_values = RoleFieldValues::where('user_id', $TaskData->user_id)->where('field_id', $form_field->field_id)->where('role_id', $TaskData->role_id)->first();

            if (!isset($role_field_values['id'])) {
                // Insert User field data
                RoleFieldValues::create($insert_data);
            } else {

                // Update user field data
                RoleFieldValues::where('id', $role_field_values['id'])->update($insert_data);
            }
        }


        /*$request = array();
        $request['name']             = $TaskData->name;
        $request['email']            = $TaskData->email;
        //$request['password']         = $TaskData->password;
        //$request['confirm_password'] = $TaskData->confirm_password;
        //$request['role']             = $TaskData->role;

        $nameRules  = array('name'  => 'required');
        $emailRules = array('email' => 'required|email|unique:users,email,'.$TaskData->user_id);
        //$passwordRules = array('password' => 'same:confirm_password');
        //$rolesRules    = array('role'     => 'required');

        $nameValidator     = Validator::make($request, $nameRules);
        $emailValidator    = Validator::make($request, $emailRules);
        //$passwordValidator = Validator::make($request, $passwordRules);
        //$rolesValidator    = Validator::make($request, $rolesRules);


        if ($nameValidator->fails()) {
            return $this->response->array([
                'message'     => 'The name field is required.',
                'status_code' => 411
            ]);
        }elseif ($emailValidator->fails()){
            return $this->response->array([
                'message'     => 'The email field is required..',
                'status_code' => 411
            ]);
        }*//*elseif ($rolesValidator->fails()){
            return $this->response->array([
                'message'     => 'The role field is required.',
                'status_code' => 411
            ]);
        }*/

        //$requests = new Request;
        //$input   = $request->all();
        /*if(!empty($request['password'])){
            if ($passwordValidator->fails()){
                return $this->response->array([
                    'message'     => 'The password and confirm-password must match.',
                    'status_code' => 411
                ]);
            }
            $request['password'] = Hash::make($request['password']);

        }else{

            $request = array_except($request,array('password'));
        }*/

        $user    = User::find($TaskData->user_id);
        $request = array();
        $request['name'] = $TaskData->name;

        if (!empty($userAvatar)){

            if(substr($userAvatar->getMimeType(), 0, 5) != 'image') {
                return $this->response->array([
                    'message'     => 'Please upload only jpeg,jpg and png image.',
                    'status_code' => 702
                ]);
            }

            if (!Storage::disk('upload')->exists('user_avatar')){
                // Create Directory
                Storage::disk('upload')->makeDirectory('user_avatar');
            }

            $storage = public_path()."/uploads/user_avatar";

            // Create A FileName From Uploaded File.
            $fileName = time().$userAvatar->getClientOriginalName();

            // Remove Spaces In The Filename And Convert To LowerCase.
            $fileName = str_replace(' ', '', strtolower($fileName));

            // Move The File To Storage Directory
            if($userAvatar->move($storage,$fileName)){
                $FileNameStore      = "/uploads/user_avatar/".$fileName;
            }

            $request['user_avatar'] = $FileNameStore;
        }

        $user->update($request);

        //DB::table('role_user')->where('user_id',$TaskData->user_id)->delete();

        //$user->attachRole($request['role']);

        return $this->response->array([
            'message'     => "User updated successfully",
            'status_code' => 200
        ]);
    }
     /**
     * Event Listing
     *
     * Request Object :
     * {"task":"eventList","taskData":{"user_id":"1"}}
     *
     */
    public function eventList($TaskData){

        $events = Events::orderBy('eventid','DESC')->paginate();

        if (empty($events)) {
            return $this->response->array([
                'data'        => "",
                'total'       => $events->Total(),
                'message'     => "No Data available.",
                'status_code' => 204
            ]);
        }

        $eventData = array();
        foreach ($events as $key => $event) {
            if ($event->status == 1) {
                $events_status = "0";
                if ($event->eventid && $TaskData->user_id) {
                    // event status for particular user
                    $events_status = UserEvents::where('user_id',$TaskData->user_id)->where('event_id',$event->eventid)->first();
                    switch ($events_status['status']) {
                        case '1':
                            // Approved
                            $status = "2";
                            break;
                        case '0':
                            // Pending
                            $status = "1";
                            break;
                        default:
                            // Not registered
                            $status = "0";
                            break;
                    }

                    $events_status = $status;
                }

                $event = array(
                    'id'                   => $event->eventid,
                    'name'                 => $event->name,
                    'short_desc'           => $event->shortdesc,
                    'description'          => $event->description,
                    'status'               => $event->status,
                    'start_date'           => $event->startdate,
                    'start_date_timestamp' => gmdate(strtotime($event->startdate)),
                    'start_time'           => $event->starttime,
                    'start_time_timestamp' => gmdate(strtotime($event->startdate . (' ') . $event->starttime)),
                    'end_date'             => $event->enddate,
                    'end_date_timestamp'   => gmdate(strtotime($event->enddate)),
                    'end_time'             => $event->endtime,
                    'endtime_timestamp'    => gmdate(strtotime($event->enddate . (' ') . $event->endtime)),
                    'added_by'             => $event->addedby,
                    'cover_image'          => ($event->cover_image) ? url('/').$event->cover_image : "",
                    'type'                 => $event->type,
                    'price'                => $event->price,
                    'venue'                => $event->venue,
                    'phone_number'         => $event->phone_number,
                    'email'                => $event->email,
                    'latitude'             => $event->latitude,
                    'longitude'            => $event->longitude,
                    'entry_pass'           => $event->entrypass,
                    'page_type'            => $event->pagetype,
                    'url'                  => $event->url,
                    'html'                 => $event->html,
                    'meals'                => $event->meals,
                    'event_status'         => $events_status,
                    );
                $eventData[] = $event;
            }
        }

        return $this->response->array([
            'data'        => $eventData,
            'total'       => $events->Total(),
            'message'     => "",
            'status_code' => 200
        ]);
    }

    /**
     * Event Details
     *
     * Request Object :
     * {"task":"eventDetail","taskData":{"event_id":"1","user_id":"1"}}
     *
     */
    public function eventDetail($TaskData){
        $eventDetail = Events::find($TaskData->event_id);

        if (empty($eventDetail)) {
            return $this->response->array([
                'data'        => "",
                'message'     => "No Data available.",
                'status_code' => 204
            ]);
        }

        $events_status = "0";
        if ($TaskData->event_id && $TaskData->user_id) {
            // event status for particular user
            $events_status = UserEvents::where('user_id',$TaskData->user_id)->where('event_id',$TaskData->event_id)->first();
            switch ($events_status['status']) {
                case '1':
                    // Approved
                    $status = "2";
                    break;
                case '0':
                    // Pending
                    $status = "1";
                    break;
                default:
                    // Not registered
                    $status = "0";
                    break;
            }

            $events_status = $status;
        }

        // Media Image
        $media_image = Media::where('event_id',$TaskData->event_id)->where('file_type','image')->get();
        $media = array(
            "image"    => array(),
            "video"    => array(),
            "document" => array()
            );

        foreach ($media_image as $key => $image) {
            $image_data = array(
                'image_path'       => ($image['file_path']) ? url('/').$image['file_path'] : "",
                'thumb_image_path' => ($image['thumb_file_path']) ? url('/').$image['thumb_file_path'] : ""
                );
            $media['image'][] = $image_data;
        }

        // Media video
        $media_video = Media::where('event_id',$TaskData->event_id)->where('file_type','video')->get();

        foreach ($media_video as $key => $video) {
            $video_data = array(
                'video_path'       => ($video['file_path']) ? url('/').$video['file_path'] : "",
                'thumb_video_path' => ($video['thumb_file_path']) ? url('/').$video['thumb_file_path'] : "",
                );
            $media['video'][] = $video_data;
        }

        // Media Document
        $media_document = Media::where('event_id',$TaskData->event_id)->where('file_type','document')->get();

        foreach ($media_document as $key => $document) {
            $document_data = array(
                'document_path'       => ($document['file_path']) ? url('/').$document['file_path'] : "",
                );
            $media['document'][] = $document_data;
        }

        // Event friends attendee
        $user_ids = UserEvents::select('user_id')->where('user_id', '!=', $TaskData->user_id)->where('event_id',$TaskData->event_id)->inRandomOrder()->paginate(4);

        foreach ($user_ids as $key => $user_id) {
            $userDetail = User::findOrFail($user_id['user_id']);
            $friend_attendee = array(
                'id'              => $userDetail->id,
                'name'            => $userDetail->name,
                'email'           => $userDetail->email,
                'phone_number'    => $userDetail->phone_number,
                'user_avatar'     => ($userDetail->user_avatar) ? url('/').$userDetail->user_avatar : ""
                );

            // Register fields for all user
            $register_field_data = Fields::where('type', 'register')->get();
            if (!empty($register_field_data[0])) {
                foreach ($register_field_data as $key => $register_field) {
                    if ($register_field->active == 1) {
                        $value = RoleFieldValues::select('value', 'is_privacy')->where('field_id', $register_field->id)->where('user_id', $user_id)->first();
                        if ($value['is_privacy'] == 0) {
                            $friend_attendee[$register_field->display_name] = isset($value['value']) ? $value['value'] : "";
                        }else{
                            $friend_attendee[$register_field->display_name] = "";
                        }
                    }
                }
            }
            $friends_attendee[] = $friend_attendee;
        }

        /*$friends_attendee[] = array(
            "id"    => "1",
            "name"  => "Dwarkesh",
            "image" => url('/')."/uploads/user_avatar/dwarkesh.jpg"
            );*/

        $event = array(
            'id'                   => $eventDetail->eventid,
            'name'                 => $eventDetail->name,
            'short_desc'           => $eventDetail->shortdesc,
            'description'          => $eventDetail->description,
            'status'               => $eventDetail->status,
            'start_date'           => $eventDetail->startdate,
            'start_date_timestamp' => strtotime($eventDetail->startdate),
            'start_time'           => $eventDetail->starttime,
            'starttime_timestamp'  => gmdate(strtotime($eventDetail->startdate . (' ') . $eventDetail->starttime)),
            'end_date'             => $eventDetail->enddate,
            'end_date_timestamp'   => gmdate(strtotime($eventDetail->enddate)),
            'end_time'             => $eventDetail->endtime,
            'endtime_timestamp'    => strtotime($eventDetail->enddate . (' ') . $eventDetail->endtime),
            'added_by'             => $eventDetail->addedby,
            'cover_image'          => ($eventDetail->cover_image) ? url('/').$eventDetail->cover_image : "",
            'type'                 => $eventDetail->type,
            'price'                => $eventDetail->price,
            'venue'                => $eventDetail->venue,
            'phone_number'         => $eventDetail->phone_number,
            'email'                => $eventDetail->email,
            'latitude'             => $eventDetail->latitude,
            'longitude'            => $eventDetail->longitude,
            'entry_pass'           => $eventDetail->entrypass,
            'page_type'            => $eventDetail->pagetype,
            'url'                  => $eventDetail->url,
            'html'                 => $eventDetail->html,
            'meals'                => $eventDetail->meals,
            'event_status'         => $events_status,
            'media'                => $media,
            'friends_attendee'     => $friends_attendee
            );

        return $this->response->array([
            'data'        => $event,
            'message'     => "",
            'status_code' => 200
        ]);
    }

    /**
     * Sessions List
     *
     * Request Object :
     * {"task":"sessionsList","taskData":{"user_id":"1", "event_id":"6"}}
     *
     */
    public function sessionsList($TaskData){

        $sessions = Sessions::with('session_sessions_speakers')->with('session_sessions_speakers.sessions_speakers_users')->with('events')->where('event_id', $TaskData->event_id)->get();

        if (empty($sessions)) {
            return $this->response->array([
                'data'        => "",
                'total'       => $sessions->count(),
                'message'     => "No Data available.",
                'status_code' => 204
            ]);
        }else{
            $sessionsData = array();
            foreach ($sessions as $key => $session) {
                $speaker_data = array();
                foreach ($session['session_sessions_speakers'] as $key => $speaker) {

                    $userDetail = User::findOrFail($speaker->speaker_id);
                    $speakers = array(
                        'id'              => $userDetail->id,
                        'Name'            => $userDetail->name,
                        'Email'           => $userDetail->email,
                        'Phone Number'    => $userDetail->phone_number,
                        'User Avatar'     => ($userDetail->user_avatar) ? url('/').$userDetail->user_avatar : ""
                        //'register_fields' => $register_fields
                        );

                    // Register fields for all user
                    $register_field_data = Fields::where('type', 'register')->get();
                    if (!empty($register_field_data[0])) {
                        foreach ($register_field_data as $key => $register_field) {
                            if ($register_field->active == 1) {
                                $value = RoleFieldValues::select('value', 'is_privacy')->where('field_id', $register_field->id)->where('user_id', $speaker->speaker_id)->first();
                                if ($value['is_privacy'] == 0) {
                                    $speakers[$register_field->display_name] = isset($value['value']) ? $value['value'] : "";
                                }else{
                                    $speakers[$register_field->display_name] = "";
                                }
                            }
                        }
                    }
                    $speaker_data[] =  $speakers;
                }

                $user_feedback_data = Feedback::where('user_id',$TaskData->user_id)->where('session_id', $session->id)->first();

                $session = array(
                    'session_id'           => $session->id,
                    'event_id'             => $session->event_id,
                    'name'                 => $session->name,
                    'description'          => $session->description,
                    'status'               => $session->status,
                    'date'                 => $session->date,
                    'date_timestamp'       => gmdate(strtotime($session->date)),
                    'start_time'           => $session->start_time,
                    'start_time_timestamp' => gmdate(strtotime($session->date . (' ') . $session->start_time)),
                    'end_time'             => $session->end_time,
                    'end_time_timestamp'   => gmdate(strtotime($session->date . (' ') . $session->end_time)),
                    'capacity'             => $session->capacity,
                    'venue'                => $session->venue,
                    'type'                 => $session->type,
                    'reviewed_text'        => $user_feedback_data['feedback'] ? $user_feedback_data['feedback'] : "",
                    'reviewed_rating'      => $user_feedback_data['star_rating'] ? $user_feedback_data['star_rating'] : "" ,
                    'speakers'             => $speaker_data
                    );

                $sessionsData[] = $session;
            }

            return $this->response->array([
                'data'        => $sessionsData,
                'total'       => $sessions->count(),
                'message'     => "",
                'status_code' => 200
            ]);
        }
    }

    /**
     * Sessions List
     *
     * Request Object :
     * {"task":"getPollingList","taskData":{"user_id":"1","session_id":"1"}}
     *
     */
    public function getPollingList($TaskData){
        $user_id    = $TaskData->user_id;
        $session_id = $TaskData->session_id;

        if (empty($user_id) || empty($session_id)) {
            return $this->response->array([
                'message'     => 'Invalid Data.',
                'status_code' => 400
            ]);
        }else{
            $polling_questions = PollingQuestion::with('question_session')->with('poll_answers')->where('session_id',$TaskData->session_id)->get();

            if (count($polling_questions) > 0){
                $data = array();
                foreach ($polling_questions as $key => $question) {

                    $user_ids     = DB::table('user_polling_answer')->where('poll_question_id', '=', $question->id)->lists('user_id');
                    $total_answer = DB::table('user_polling_answer')->where('poll_question_id', '=', $question->id)->count();

                    if ($total_answer > 0) {
                        $answer_one_count   = UserPollingAnswer::where('poll_answer_id', $question['poll_answers']['0']->id)->count();
                        $answer_two_count   = UserPollingAnswer::where('poll_answer_id', $question['poll_answers']['1']->id)->count();
                        $answer_three_count = UserPollingAnswer::where('poll_answer_id', $question['poll_answers']['2']->id)->count();
                        $answer_four_count  = UserPollingAnswer::where('poll_answer_id', $question['poll_answers']['3']->id)->count();
                    }

                    $answer1 = new stdClass;
                    $answer1->answer_id = (int)$question['poll_answers']['0']->id;
                    $answer1->answer    = $question['poll_answers']['0']->answer;
                    $answer1->result    = ($total_answer == 0) ? 0 : number_format($answer_one_count*100/$total_answer, 2, '.', '');

                    $answer2 = new stdClass;
                    $answer2->answer_id = (int)$question['poll_answers']['1']->id;
                    $answer2->answer    = $question['poll_answers']['1']->answer;
                    $answer2->result    = ($total_answer == 0) ? 0 : number_format($answer_two_count*100/$total_answer, 2, '.', '');

                    $answer3 = new stdClass;
                    $answer3->answer_id = (int)$question['poll_answers']['2']->id;
                    $answer3->answer    = $question['poll_answers']['2']->answer;
                    $answer3->result    = ($total_answer == 0) ? 0 : number_format($answer_three_count*100/$total_answer, 2, '.', '');

                    $answer4 = new stdClass;
                    $answer4->answer_id = (int)$question['poll_answers']['3']->id;
                    $answer4->answer    = $question['poll_answers']['3']->answer;
                    $answer4->result    = ($total_answer == 0) ? 0 : number_format($answer_four_count*100/$total_answer, 2, '.', '');

                    $question_data = '';
                    $question_data = array(
                        'session_id'       => (int)$question->session_id,
                        'poll_question_id' => (int)$question->id,
                        'is_answered'      => (in_array($user_id, $user_ids)) ? 1 : 0,
                        'polling_question' => $question->question,
                        'answer1'          => $answer1,
                        'answer2'          => $answer2,
                        'answer3'          => $answer3,
                        'answer4'          => $answer4
                    );

                    $data[] = $question_data;
                }
                return $this->response->array([
                    'data'        => $data,
                    'status_code' => 200
                ]);
            }else{
                return $this->response->array([
                    'data'        => "",
                    'status_code' => 204
                ]);
            }
        }
    }

    /**
     * Save user polling answer data
     *
     * {"task":"saveUserPollingAnswer","taskData":{"user_id":"1", "session_id":"1", "poll_question_id":"1", "poll_answer_id":"1"}}
     *
     */
    public function saveUserPollingAnswer($TaskData){

        if (empty($TaskData->user_id) || empty($TaskData->session_id) || empty($TaskData->poll_question_id) || empty($TaskData->poll_answer_id)) {
            return $this->response->array([
                'message'     => 'Invalid Data.',
                'status_code' => 400
            ]);
        }else{
            $polling_data = array(
                'user_id'          => $TaskData->user_id,
                'session_id'       => $TaskData->session_id,
                'poll_question_id' => $TaskData->poll_question_id,
                'poll_answer_id'   => $TaskData->poll_answer_id
            );

            // Insert Polling Data
            UserPollingAnswer::insert($polling_data);

            $polling_question = PollingQuestion::where('id', $TaskData->poll_question_id)->first();
            $poll_answers     = PollingAnswer::where('question_id', $polling_question->id)->get();
            $user_ids         = UserPollingAnswer::where('poll_question_id', $polling_question->id)->lists('user_id')->all();
            $total_answer     = UserPollingAnswer::where('poll_question_id', $polling_question->id)->count();

            if ($total_answer > 0) {
                $answer_one_count   = UserPollingAnswer::where('poll_answer_id', $poll_answers['0']->id)->count();
                $answer_two_count   = UserPollingAnswer::where('poll_answer_id', $poll_answers['1']->id)->count();
                $answer_three_count = UserPollingAnswer::where('poll_answer_id', $poll_answers['2']->id)->count();
                $answer_four_count  = UserPollingAnswer::where('poll_answer_id', $poll_answers['3']->id)->count();
            }

            $answer1 = new stdClass;
            $answer1->answer_id = (int)$poll_answers['0']->id;
            $answer1->answer    = $poll_answers['0']->answer;
            $answer1->result    = ($total_answer == 0) ? 0 : number_format($answer_one_count*100/$total_answer, 2, '.', '');

            $answer2 = new stdClass;
            $answer2->answer_id = (int)$poll_answers['1']->id;
            $answer2->answer    = $poll_answers['1']->answer;
            $answer2->result    = ($total_answer == 0) ? 0 : number_format($answer_two_count*100/$total_answer, 2, '.', '');

            $answer3 = new stdClass;
            $answer3->answer_id = (int)$poll_answers['2']->id;
            $answer3->answer    = $poll_answers['2']->answer;
            $answer3->result    = ($total_answer == 0) ? 0 : number_format($answer_three_count*100/$total_answer, 2, '.', '');

            $answer4 = new stdClass;
            $answer4->answer_id = (int)$poll_answers['3']->id;
            $answer4->answer    = $poll_answers['3']->answer;
            $answer4->result    = ($total_answer == 0) ? 0 : number_format($answer_four_count*100/$total_answer, 2, '.', '');

            $question_data = '';
            $question_data = array(
                'session_id'       => (int)$polling_question->session_id,
                'poll_question_id' => (int)$polling_question->id,
                'is_answered'      => (in_array($TaskData->user_id, $user_ids)) ? 1 : 0,
                'polling_question' => $polling_question->question,
                'answer1'          => $answer1,
                'answer2'          => $answer2,
                'answer3'          => $answer3,
                'answer4'          => $answer4
            );

            $data[] = $question_data;
        }
        return $this->response->array([
            'data'        => $data,
            'status_code' => 200
        ]);
    }

    /**
     * Store Feedback in database given by user.
     *
     * {"task":"saveFeedback","taskData":{"user_id":"1","session_id":"1","feedback":"feedback","star_rating":"2.5","status":"1"}}
     *
     * "user_id":"user's ID"
     */
    public function saveFeedback($TaskData){

        if ($TaskData->star_rating == '') {
            return $this->response->array([
                    'message'     => "Please give rating.",
                    'status_code' => 400
                ]);
        }else{

            $feedback_data = array(
                'feedback'    => $TaskData->feedback,
                'user_id'     => $TaskData->user_id,
                'session_id'  => $TaskData->session_id,
                'star_rating' => $TaskData->star_rating,
                'status'      => $TaskData->status
            );

            $feedback_data_exist = Feedback::where('user_id', $TaskData->user_id)->where('session_id', $TaskData->session_id)->first();

            if (!isset($feedback_data_exist['id'])) {
                // Insert Feedback Data
                $insert_data = Feedback::create($feedback_data);
            } else {
                return $this->response->array([
                    'message'     => "You have already reviewed this session.",
                    'status_code' => 200
                ]);
            }

            if (!empty($insert_data)) {
                return $this->response->array([
                    'message'     => "Thanks for your feedback.",
                    'status_code' => 200
                ]);
            }else{
                return $this->response->array([
                    'message'     => "",
                    'status_code' => 400
                ]);
            }
        }
    }

    /**
     * Store Questions in database asked by user.
     *
     * {"task":"saveAskQuestion","taskData":{"user_id":"1","session_id":"1","question":"Question","status":"1"}}
     *
     * "user_id":"user's ID"
     */
    public function saveAskQuestion($TaskData){

        if ($TaskData->question == '') {
            return $this->response->array([
                    'message'     => "Please write Question.",
                    'status_code' => 400
                ]);
        }else{

            $question_data = array(
                'user_id'    => $TaskData->user_id,
                'session_id' => $TaskData->session_id,
                'status'     => $TaskData->status,
                'question'   => $TaskData->question
            );

            // Insert Feedback Data
            $insert_data = Question::create($question_data);

            if (!empty($insert_data)) {
                return $this->response->array([
                    'message'     => "Thanks for asking question.",
                    'status_code' => 200
                ]);
            }else{
                return $this->response->array([
                    'message'     => "",
                    'status_code' => 400
                ]);
            }
        }
    }

    /*
    *   Get menu items
    *
    * {"task":"getMenuItem","taskData":{}}
    *
    */
    public function getMenuItem($TaskData){
        $file = file_get_contents(public_path() .'/menu.txt');
        $json = json_decode($file);

        return $this->response->array([
                    'status_code' => $json->code,
                    'menuitems'   => $json->menuitems
                ]);
    }

    /*
    *   Send user request for particular session
    *
    * {"task":"sendUserRequestForSession","taskData":{"user_id":"2","session_id":"2"}}
    *
    */
    public function sendUserRequestForSession($TaskData){
        if ($TaskData->user_id == '' && $TaskData->session_id == '') {
            return $this->response->array([
                    'message'     => "Please send proper data.",
                    'status_code' => 400
                ]);
        }else{

            $data = array(
                'user_id'    => $TaskData->user_id,
                'session_id' => $TaskData->session_id,
                'status'     => '0'
            );

            // Insert User Request For Session Data
            $insert_data = UserRequestForSession::create($data);

            if (!empty($insert_data)) {
                return $this->response->array([
                    'message'     => "Your request sent to Administrator.",
                    'status_code' => 200
                ]);
            }else{
                return $this->response->array([
                    'message'     => "",
                    'status_code' => 400
                ]);
            }
        }
    }

    /*
    *   Send user request for particular event
    *
    * {"task":"sendUserRequestForEvent","taskData":{"user_id":"2","event_id":"2"}}
    *
    */
    public function sendUserRequestForEvent($TaskData){
        if ($TaskData->user_id == '' || $TaskData->event_id == '') {
            return $this->response->array([
                    'message'     => "Please send proper data.",
                    'status_code' => 400
                ]);
        }else{

            // Insert User Request For Event Data
            $insert_data = $this->userRequestForEvent($TaskData->user_id, $TaskData->event_id);

            $userDetail  =  User::findOrFail($TaskData->user_id);

            //User Data
            $user_data = array(
                'id'           => $userDetail->id,
                'name'         => $userDetail->name,
                'email'        => $userDetail->email,
                'phone_number' => $userDetail->phone_number,
                'user_avatar'  => ($userDetail->user_avatar) ? url('/').$userDetail->user_avatar : "",
                );

            if (!empty($insert_data)) {
                return $this->response->array([
                    'user_data'   => $user_data,
                    'event_data'  => $insert_data['event_data'],
                    'message'     => "Your request sent to Administrator.",
                    'status_code' => 200
                ]);
            }else{
                return $this->response->array([
                    'message'     => "",
                    'status_code' => 400
                ]);
            }
        }
    }

    /*
    *   Send user request for particular event
    *
    *   Calling from user login and registration web services
    */
    public function userRequestForEvent($user_id,$event_id){
        if ($event_id > 0 && $user_id  > 0) {
            $event_status = Events::select('type')->where('eventid', $event_id)->first();

            switch ($event_status->type) {
                case 'inviteonly':
                    $status = "0";
                    break;
                case 'open':
                    $status = "1";
                    break;
                default:
                    $status = "";
                    break;
            }

            $insert_data = array(
                'user_id'  => $user_id,
                'event_id' => $event_id,
                'status'   => $status
            );

            $user_event_exist = UserEvents::where('user_id', $user_id)->where('event_id', $event_id)->first();

            if (!isset($user_event_exist['id'])) {
                // Insert User Request For event data
                UserEvents::create($insert_data);
            }

        }

        $data = array(
            "event_data" => array()
            );

        if ($user_id) {
            $user_events_data = UserEvents::with('userevent_user')->with('userevent_event')->where('user_id',$user_id)->get();

            foreach ($user_events_data as $key => $user_events) {
                $events = array();
                if($user_events['status'] == 1) {
                    $event_data = array(
                        "event_id"             => $user_events['event_id'],
                        "name"                 => $user_events->userevent_event['name'],
                        'start_date'           => $user_events->userevent_event['startdate'],
                        'start_date_timestamp' => gmdate(strtotime($user_events->userevent_event['startdate'])),
                        'start_time'           => $user_events->userevent_event['starttime'],
                        'start_time_timestamp' => gmdate(strtotime($user_events->userevent_event['startdate'] . (' ') . $user_events->userevent_event['starttime'])),
                        'end_date'             => $user_events->userevent_event['enddate'],
                        'end_date_timestamp'   => gmdate(strtotime($user_events->userevent_event['enddate'])),
                        'end_time'             => $user_events->userevent_event['endtime'],
                        'endtime_timestamp'    => gmdate(strtotime($user_events->userevent_event['enddate'] . (' ') . $user_events->userevent_event['endtime'])),
                        "event_user_roles"     => array()
                        );
                    $roles_data = explode(',', $user_events->userevent_event['roles']);

                // Get User roles data
                $user_roles_data = User::where('id', $user_id)->get();
                $user_role_ids   = array();

                foreach ($user_roles_data as $key => $user_role) {
                    if(!empty($user_role->roles)){
                        foreach ($user_role->roles as $key => $user_role) {
                            $user_role_ids[] = $user_role->id;
                        }
                    }
                }

                foreach ($roles_data as $roles_data_key => $role_id) {
                    $roles = Role::where('id', $role_id)->get();
                    foreach ($roles as $roles_key => $role) {
                        foreach ($user_role_ids as $user_role_id) {
                            if ($user_role_id == $role->id) {
                                $event_user_roles_data = array(
                                    "name"  => $role->display_name,
                                    "value" => $role->id
                                    );
                                $event_data['event_user_roles'][] = $event_user_roles_data;
                            }
                        }
                    }
                }

                   $data['event_data'][] = $event_data;
               }
            }

           /*$roles_data = User::where('id', $user_id)->get();
            foreach ($roles_data as $key => $role) {
                if(!empty($role->roles)){
                    foreach ($role->roles as $key => $role) {
                        $data['user_roles'][$key]['name']  = $role->display_name;
                        $data['user_roles'][$key]['value'] = $role->id;
                    }
                }
            }*/
        }

        return $data;
    }

    /*
    *   Add to my agenda
    *
    * {"task":"addToMyAgenda","taskData":{"user_id":"2","event_id":"6","session_id":"2","note_text":"Note text", "date":"2017-02-17", "time":"11:12:00","exhibitor_id":"1","meetup_id":"1","type":"session/exhibitor/note/meetup"}}
    *
    */
    public function addToMyAgenda($TaskData){

        if ($TaskData->user_id == '' || $TaskData->type == '' || $TaskData->event_id == '') {
            return $this->response->array([
                    'message'     => "Please send proper data.",
                    'status_code' => 400
                ]);
        }else{

            $data = array(
                "user_id"      => $TaskData->user_id,
                "event_id"     => $TaskData->event_id,
                "session_id"   => $TaskData->session_id,
                "exhibitor_id" => $TaskData->exhibitor_id,
                "note_text"    => $TaskData->note_text,
                "date"         => $TaskData->date,
                "time"         => $TaskData->time,
                "meetup_id"    => $TaskData->meetup_id,
                "type"         => $TaskData->type
            );

            if ($TaskData->session_id)
            {
                $my_agenda_exist = MyAgenda::where('user_id', $TaskData->user_id)->where('event_id', $TaskData->event_id)->where('type', $TaskData->type)->where('session_id', $TaskData->session_id)->first();
            }
            else if ($TaskData->meetup_id)
            {
                $my_agenda_exist = MyAgenda::where('user_id', $TaskData->user_id)->where('event_id', $TaskData->event_id)->where('type', $TaskData->type)->where('meetup_id', $TaskData->meetup_id)->first();
            }
            else if ($TaskData->exhibitor_id)
            {
                $my_agenda_exist = MyAgenda::where('user_id', $TaskData->user_id)->where('event_id', $TaskData->event_id)->where('type', $TaskData->type)->where('exhibitor_id', $TaskData->exhibitor_id)->first();
            }

            if (!isset($my_agenda_exist['id'])) {
                // Insert my agenda Data
                $insert_data = MyAgenda::create($data);
            }
            else if ($TaskData->type == "note")
            {
                $insert_data = MyAgenda::create($data);
            }

            if (!empty($insert_data)) {
                return $this->response->array([
                    'message'     => "My agenda data added successfully.",
                    'status_code' => 200
                ]);
            }else{
                return $this->response->array([
                    'message'     => "Already data Exist",
                    'status_code' => 400
                ]);
            }
        }
    }

    /*
    *   Get to my agenda
    *
    * {"task":"getMyAgenda","taskData":{"user_id":"1","event_id":"6"}}
    *
    */
    public function getMyAgenda($TaskData){
        if ($TaskData->user_id == '' || $TaskData->event_id == '') {
            return $this->response->array([
                    'message'     => "Please send proper data.",
                    'status_code' => 400
                ]);
        }else{

            // Get my agenda Data
            $my_agenda_data = MyAgenda::where('user_id', $TaskData->user_id)->where('event_id', $TaskData->event_id)->get();

            $agenda_data = array();
            $data = array(
                "agenda_id"   => "",
                "agenda_type" => "",
                "agenda_data" => ""
                );
            foreach ($my_agenda_data as $key => $my_agenda) {

                switch ($my_agenda['type']) {
                    case 'note':
                        $data['agenda_id']   = $my_agenda['id'];
                        $data['agenda_type'] = $my_agenda['type'];
                        $note = array(
                            "note" => $my_agenda['note_text'],
                            "date" => $my_agenda['date'],
                            "time" => $my_agenda['time']
                            );

                        $data['agenda_data'] = $note;
                        break;

                    case 'session':
                        $data['agenda_id']   = $my_agenda['id'];
                        $data['agenda_type'] = $my_agenda['type'];

                        $sessions = Sessions::with('session_sessions_speakers')->with('session_sessions_speakers.sessions_speakers_users')->with('events')->where('id', $my_agenda['session_id'])->get();
                        $speaker_data = array();
                        if (!empty($sessions)) {
                            foreach ($sessions as $key => $session) {
                                foreach ($session['session_sessions_speakers'] as $key => $speaker) {
                                    $userDetail = User::findOrFail($speaker->speaker_id);
                                    $speaker_users = array(
                                        'id'           => $userDetail->id,
                                        'name'         => $userDetail->name,
                                        'email'        => $userDetail->email,
                                        'phone_number' => $userDetail->phone_number,
                                        'user_avatar'  => ($userDetail->user_avatar) ? url('/').$userDetail->user_avatar : ""
                                        );

                                    $speaker_data[] =  $speaker_users;
                                }

                                $session = array(
                                    'session_id'           => $session->id,
                                    'event_id'             => $session->event_id,
                                    'name'                 => $session->name,
                                    'description'          => $session->description,
                                    'status'               => $session->status,
                                    'date'                 => $session->date,
                                    'date_timestamp'       => gmdate(strtotime($session->date)),
                                    'start_time'           => $session->start_time,
                                    'start_time_timestamp' => gmdate(strtotime($session->date . (' ') . $session->start_time)),
                                    'end_time'             => $session->end_time,
                                    'end_time_timestamp'   => gmdate(strtotime($session->date . (' ') . $session->end_time)),
                                    'capacity'             => $session->capacity,
                                    'venue'                => $session->venue,
                                    'type'                 => $session->type,
                                    'speakers'             => array()
                                    );

                                $session['speakers'] = $speaker_data;
                            }
                            $data['agenda_data'] = $session;
                        }
                        break;

                    case 'meetup':
                        $data['agenda_id']   = $my_agenda['id'];
                        $data['agenda_type'] = $my_agenda['type'];

                        $meetup = Meetups::where('to_user_id', $TaskData->user_id)->where('id', $my_agenda['meetup_id'])->first();

                        if (!empty($meetup)) {
                            $userDetail = User::findOrFail($meetup['user_id']);
                            $users = array(
                                    'id'           => $userDetail->id,
                                    'name'         => $userDetail->name,
                                    'email'        => $userDetail->email,
                                    'phone_number' => $userDetail->phone_number,
                                    'user_avatar'  => ($userDetail->user_avatar) ? url('/').$userDetail->user_avatar : ""
                                    );

                            $meetup = array(
                                "user"          => $users,
                                "meetup_agenda" => $meetup['meetup_agenda'],
                                "status"        => $meetup['status'],
                                "date"          => $meetup['date'],
                                "time"          => $meetup['time']
                                );

                            $data['agenda_data'] = $meetup;
                        }

                        $meetup = Meetups::where('user_id', $TaskData->user_id)->where('id', $my_agenda['meetup_id'])->first();

                        if (!empty($meetup)) {
                            $userDetail = User::findOrFail($meetup['to_user_id']);
                            $users = array(
                                    'id'           => $userDetail->id,
                                    'name'         => $userDetail->name,
                                    'email'        => $userDetail->email,
                                    'phone_number' => $userDetail->phone_number,
                                    'user_avatar'  => ($userDetail->user_avatar) ? url('/').$userDetail->user_avatar : ""
                                    );

                            $meetup = array(
                                "user"          => $users,
                                "meetup_agenda" => $meetup['meetup_agenda'],
                                "status"        => $meetup['status'],
                                "date"          => $meetup['date'],
                                "time"          => $meetup['time']
                                );

                            $data['agenda_data'] = $meetup;
                        }

                        break;
                    default:
                        # code...
                        break;
                }
                $agenda_data[] = $data;
            }

            if (!empty($agenda_data)) {
                return $this->response->array([
                    'data'        => $agenda_data,
                    'message'     => "Get My agenda data successfully.",
                    'status_code' => 200
                ]);
            }else{
                return $this->response->array([
                    'message'     => "No Data Found",
                    'status_code' => 204
                ]);
            }
        }
    }

    /*
    *   get ticket list as per event_id
    *
    *   reqObject = {"task":"getTicketList","taskData":{"user_id":"1","event_id":"1"}}
    */
    public function getTicketList($TaskData)
    {
        if ($TaskData->event_id == '')
        {
            return $this->response->array([
                    'message'     => "Please send proper data.",
                    'status_code' => 400
                ]);
        }
        else
        {
            $ticketLists = EventTickets::where('event_id',$TaskData->event_id)->get();

            if (count($ticketLists) > 0){
                $data = array();
                foreach ($ticketLists as $key => $tktdata)
                {
                    $purchasedTicket = EventTicketsUser::select('events_tickets_user.ticket_id')->where('event_id',$TaskData->event_id)->where('user_id',$TaskData->user_id)->first();

                    if($purchasedTicket)
                    {
                        if($purchasedTicket->ticket_id == $tktdata->id)
                        {
                            $status = 1;
                            $canPurchase = 1;
                        }
                        else
                        {
                            $status = 0;
                            $canPurchase = 0;
                        }
                    }
                    else
                    {
                        $status = 0;
                        $canPurchase = 0;
                    }


                    $ticket_data = '';
                    $ticket_data = array(
                        'ticket_id'           => (int)$tktdata->id,
                        'ticket_title'        => $tktdata->title,
                        'ticket_short_desc'   => $tktdata->short_desc,
                        'ticket_full_desc'    => $tktdata->full_desc,
                        'ticket_price'        => $tktdata->ticket_price,
                        'ticket_currency'     => $tktdata->ticket_currency,
                        'ticket_image'        => ($tktdata->ticket_image) ? asset($tktdata->ticket_image) : "",
                        'ticket_status'       => $status,
                        'ticket_can_purchase' => $canPurchase,
                        'ticket_purchase_url' => 'http://tasolglobal.com'

                    );
                    $data[] = $ticket_data;
                }

                return $this->response->array([
                    'data' => $data,
                    'status_code' => 200
                ]);
            }
            else{
                return $this->response->array([
                    'message'        => "NO Data Available!",
                    'status_code' => 204
                ]);
            }
        }
    }

    /*
    *   get attendee list as per event_id
    *
    *   reqObject = {"task":"getAttendeeList","taskData":{"user_id":"","event_id":"1","page_no":"1","keyword":"","criteria":"{}"}}
    */

    public function getAttendeeList($TaskData)
    {
        if ($TaskData->event_id == '')
        {
            return $this->response->array([
                    'message'     => "Please send proper data.",
                    'status_code' => 400
                ]);
        }
        else
        {
            if($TaskData->keyword)
            {
               $attendeeList = UserEvents::with('userevent_user')->with('userevent_event')->join('users', 'users.id', '=', 'user_id')->where('event_id',$TaskData->event_id)->where('users.name','like', '%'.$TaskData->keyword.'%')->paginate(10);
            }
            else if ($TaskData->criteria)
            {
                $attendeeList = UserEvents::with('userevent_user')->with('userevent_event')->join('users', 'users.id', '=', 'user_id')->where('event_id',$TaskData->event_id)->where('users.name','like', '%'.$TaskData->criteria.'%')->paginate(10);
            }
            else
            {
                $attendeeList = UserEvents::with('userevent_user')->with('userevent_event')->where('event_id',$TaskData->event_id)->paginate(10);
            }

            if (count($attendeeList) > 0)
            {
                $data = array();
                foreach ($attendeeList as $key => $attendee)
                {
                    if ($attendee->user_id != $TaskData->user_id) {
                        $user  = User::find($attendee->user_id);
                        $roles = "";
                        if(!empty($user->roles)){
                            foreach($user->roles as $v){
                                $roles[] = $v->display_name;
                            }
                            $roles = implode(', ', $roles);
                        }

                        // Register fields for all user
                        $register_field_data = Fields::where('type', 'register')->get();

                        $register_fields = array();
                        if (!empty($register_field_data[0])) {
                            foreach ($register_field_data as $key => $register_field) {
                                if ($register_field->active == 1) {
                                    $value = RoleFieldValues::select('value', 'is_privacy')->where('field_id', $register_field->id)->where('user_id', $attendee->user_id)->first();
                                    if ($value['is_privacy'] == 0) {
                                        $register_fields[$register_field->display_name] = isset($value['value']) ? $value['value'] : "";
                                    }else{
                                        $register_fields[$register_field->display_name] = "";
                                    }
                                }
                            }
                        }

                        $attendee_data = '';
                        $attendee_data = array(
                            'attendee_id'           => isset($attendee->user_id) ? (int)$attendee->user_id : "",
                            'attendee_name'         => isset($attendee->userevent_user['name']) ? $attendee->userevent_user['name'] : "",
                            'attendee_email'        => isset($attendee->userevent_user['email']) ? $attendee->userevent_user['email'] : "",
                            'attendee_phone_number' => isset($attendee->userevent_user['phone_number']) ? $attendee->userevent_user['phone_number'] : "",
                            'attendee_user_avatar'  => $attendee->userevent_user['user_avatar'] ? asset($attendee->userevent_user['user_avatar']) : "",
                            'attendee_role'         => $roles,
                            'major_id'              => "1008",
                            'minor_id'              => ++$key,
                            'register_fields'       => $register_fields
                        );
                        $data['attendee'][] = $attendee_data;
                    }
                }
                $data['suggested_attendee'][] = $attendee_data;
                return $this->response->array([
                    'data'         => $data,
                    'total'        => $attendeeList->total(),
                    'per_page'     => $attendeeList->perPage(),
                    'current_page' => $attendeeList->currentPage(),
                    'last_page'    => $attendeeList->lastPage(),
                    'status_code'  => 200
                ]);
            }
            else
            {
                return $this->response->array([
                    'message'     => "NO Data Available!",
                    'status_code' => 204
                ]);
            }
        }
    }

    /*
    *  Send request for meetups
    *
    * {"task":"sendMeetUpRequest","taskData":{"user_id":"1","event_id":"1","to_user_id":"2","meetup_agenda":"I want to meet you","date":"2017-02-17","time":"11:40"}}
    *
    */
    public function sendMeetUpRequest($TaskData)
    {
        if ($TaskData->user_id == '' || $TaskData->event_id == '' || $TaskData->to_user_id == '') {
            return $this->response->array([
                    'message'     => "Please send proper data.",
                    'status_code' => 400
                ]);
        }else{


            // Insert meetups
            $insert_data = array(
                'user_id'       => $TaskData->user_id,
                'to_user_id'    => $TaskData->to_user_id,
                'event_id'      => $TaskData->event_id,
                'meetup_agenda' => $TaskData->meetup_agenda,
                'status'        => 0,
                'date'          => $TaskData->date,
                'time'          => $TaskData->time,
                'updated_at'    => date('Y-m-d h:i:s'),
                'created_at'    => date('Y-m-d h:i:s')
            );

            // store data in meetups.
            $result = Meetups::create($insert_data);
            if ($result->wasRecentlyCreated) {
                return $this->response->array([
                    'message'     => "Your request sent to Attendee for meetup.",
                    'status_code' => 200
                ]);
            }else{
                return $this->response->array([
                    'message'     => "",
                    'status_code' => 400
                ]);
            }
        }
    }

    /*
    *  Get meetups List
    *
    * {"task":"getMeetupList","taskData":{"user_id":"1","event_id":"6"}}
    *
    */
    public function getMeetupList($TaskData) {
        if ($TaskData->user_id == '' || $TaskData->event_id == '') {
                return $this->response->array([
                        'message'     => "Please send proper data.",
                        'status_code' => 400
                    ]);
        }else{
            // Planned meeting data
            $planned_meetup_data = Meetups::where('user_id', $TaskData->user_id)->where('event_id', $TaskData->event_id)->Where('status', 1)->get();
            $meetups = array();
            foreach ($planned_meetup_data as $key => $planned_meetup) {

                    // Register fields for all user
                    $register_field_data = Fields::where('type', 'register')->get();

                    $register_fields = array();
                    if (!empty($register_field_data[0])) {
                        foreach ($register_field_data as $key => $register_field) {
                            if ($register_field->active == 1) {
                                $value = RoleFieldValues::select('value', 'is_privacy')->where('field_id', $register_field->id)->where('user_id', $planned_meetup['to_user_id'])->first();
                                if ($value['is_privacy'] == 0) {
                                    $register_fields[$register_field->display_name] = isset($value['value']) ? $value['value'] : "";
                                }else{
                                    $register_fields[$register_field->display_name] = "";
                                }
                            }
                        }
                    }

                    $userDetail = User::findOrFail($planned_meetup['to_user_id']);
                    $users = array(
                        'id'              => $userDetail->id,
                        'name'            => $userDetail->name,
                        'email'           => $userDetail->email,
                        'phone_number'    => $userDetail->phone_number,
                        'user_avatar'     => ($userDetail->user_avatar) ? url('/').$userDetail->user_avatar : "",
                        'register_fields' => $register_fields
                        );

                    $planned_meetup = array(
                        "user"          => $users,
                        "meetup_id"     => $planned_meetup['id'],
                        "meetup_agenda" => $planned_meetup['meetup_agenda'],
                        "status"        => $planned_meetup['status'],
                        "date"          => $planned_meetup['date'],
                        "time"          => $planned_meetup['time']
                        );
                $meetups[] = $planned_meetup;
            }

            // Planned meeting data
            $planned_meetup_data = Meetups::where('to_user_id', $TaskData->user_id)->where('event_id', $TaskData->event_id)->Where('status', 1)->get();

            foreach ($planned_meetup_data as $key => $planned_meetup) {

                    // Register fields for all user
                    $register_field_data = Fields::where('type', 'register')->get();

                    $register_fields = array();
                    if (!empty($register_field_data[0])) {
                        foreach ($register_field_data as $key => $register_field) {
                            if ($register_field->active == 1) {
                                $value = RoleFieldValues::select('value', 'is_privacy')->where('field_id', $register_field->id)->where('user_id', $planned_meetup['user_id'])->first();
                                if ($value['is_privacy'] == 0) {
                                    $register_fields[$register_field->display_name] = isset($value['value']) ? $value['value'] : "";
                                }else{
                                    $register_fields[$register_field->display_name] = "";
                                }
                            }
                        }
                    }

                    $userDetail = User::findOrFail($planned_meetup['user_id']);
                    $users = array(
                        'id'              => $userDetail->id,
                        'name'            => $userDetail->name,
                        'email'           => $userDetail->email,
                        'phone_number'    => $userDetail->phone_number,
                        'user_avatar'     => ($userDetail->user_avatar) ? url('/').$userDetail->user_avatar : "",
                        'register_fields' => $register_fields
                        );

                    $planned_meetup = array(
                        "user"          => $users,
                        "meetup_id"     => $planned_meetup['id'],
                        "meetup_agenda" => $planned_meetup['meetup_agenda'],
                        "status"        => $planned_meetup['status'],
                        "date"          => $planned_meetup['date'],
                        "time"          => $planned_meetup['time']
                        );
                $meetups[] = $planned_meetup;
            }

            // Get pending request data
            $pending_req_data = Meetups::Where('to_user_id', $TaskData->user_id)->where('event_id', $TaskData->event_id)->Where('status', 0)->get();

            $pending_reqs = array();
            foreach ($pending_req_data as $key => $pending_req) {

                // Register fields for all user
                $register_field_data = Fields::where('type', 'register')->get();

                $register_fields = array();
                if (!empty($register_field_data[0])) {
                    foreach ($register_field_data as $key => $register_field) {
                        if ($register_field->active == 1) {
                            $value = RoleFieldValues::select('value', 'is_privacy')->where('field_id', $register_field->id)->where('user_id', $pending_req['user_id'])->first();
                            if ($value['is_privacy'] == 0) {
                                $register_fields[$register_field->display_name] = isset($value['value']) ? $value['value'] : "";
                            }else{
                                $register_fields[$register_field->display_name] = "";
                            }
                        }
                    }
                }

                $userDetail = User::findOrFail($pending_req['user_id']);
                $users = array(
                    'id'              => $userDetail->id,
                    'name'            => $userDetail->name,
                    'email'           => $userDetail->email,
                    'phone_number'    => $userDetail->phone_number,
                    'user_avatar'     => ($userDetail->user_avatar) ? url('/').$userDetail->user_avatar : "",
                    'register_fields' => $register_fields
                    );

                $pending_req = array(
                    "user"          => $users,
                    "meetup_id"     => $pending_req['id'],
                    "meetup_agenda" => $pending_req['meetup_agenda'],
                    "status"        => $pending_req['status'],
                    "date"          => $pending_req['date'],
                    "time"          => $pending_req['time']
                    );
                $pending_reqs[] = $pending_req;
            }

            if (!empty($planned_meetup_data || $pending_req_data)) {
                return $this->response->array([
                    'planned_meetups' => $meetups,
                    'pending_reqs'    => $pending_reqs,
                    'message'         => "Get Meetups data successfully.",
                    'status_code'     => 200
                ]);
            }else{
                return $this->response->array([
                    'message'     => "No Data Found",
                    'status_code' => 204
                ]);
            }
        }
    }

    /*
    *  Send Message to attendee(other user).
    *
    * {"task":"sendMessages","taskData":{"user_id":"1","event_id":"1","to_user_id":"2","message":"I want to meet you"}}
    *
    */
    public function sendMessages($TaskData)
    {
        if ($TaskData->user_id == '' || $TaskData->event_id == '' || $TaskData->to_user_id == '') {
            return $this->response->array([
                    'message'     => "Please send proper data.",
                    'status_code' => 400
                ]);
        }else{

            // Insert meetups
            $insert_data = array(
                'user_id'    => $TaskData->user_id,
                'to_user_id' => $TaskData->to_user_id,
                'event_id'   => $TaskData->event_id,
                'message'    => $TaskData->message,
            );

            // store data in meetups.
            $result = Messages::create($insert_data);
            if ($result->wasRecentlyCreated) {
                return $this->response->array([
                    'message'     => "Message sent successfully.",
                    'status_code' => 200
                ]);
            }else{
                return $this->response->array([
                    'message'     => "",
                    'status_code' => 400
                ]);
            }
        }
    }


    /*
    *  Send Message to attendee(other user).
    *
    * {"task":"sendRequestForMeetups","taskData":{"user_id":"1","meetup_id":"1","status":"1 or 2", "event_id":""}}
    * Status 1 means "Accept" and 2 means "Reject".
    *
    */
    public function sendRequestForMeetups($TaskData)
    {
        if ($TaskData->user_id == '' || $TaskData->meetup_id == '')
        {
            return $this->response->array([
                    'message'     => "Please send proper data.",
                    'status_code' => 400
                ]);
        }
        else
        {
            $Meetups = Meetups::find($TaskData->meetup_id);
            $Meetups->status = $TaskData->status;
            $result = $Meetups->save();

            if ($TaskData->status == 1) {
                $data = array(
                    "user_id"      => $TaskData->user_id,
                    "event_id"     => $TaskData->event_id,
                    "session_id"   => 0,
                    "exhibitor_id" => 0,
                    "note_text"    => "",
                    "date"         => "",
                    "time"         => "",
                    "meetup_id"    => $TaskData->meetup_id,
                    "type"         => "meetup"
                );

                // Insert my data in my agenda
                MyAgenda::create($data);

                $data = array(
                    "user_id"      => $Meetups->user_id,
                    "event_id"     => $TaskData->event_id,
                    "session_id"   => 0,
                    "exhibitor_id" => 0,
                    "note_text"    => "",
                    "date"         => "",
                    "time"         => "",
                    "meetup_id"    => $TaskData->meetup_id,
                    "type"         => "meetup"
                );

                // Insert my data in my agenda
                MyAgenda::create($data);
            }

            if ($result) {
                return $this->response->array([
                    'message'     => "Status change successfully.",
                    'status_code' => 200
                ]);
            }else{
                return $this->response->array([
                    'message'     => "",
                    'status_code' => 400
                ]);
            }
        }
    }

    /*
    *   get Suggeted attendee list as for meetups
    *
    *   reqObject = {"task":"getSuggestedAttendeeListForMeetups","taskData":{"event_id":"1","user_id":"1"}}
    *
    *   page = 1
    */

    public function getSuggestedAttendeeListForMeetups($TaskData)
    {
        if ($TaskData->event_id == '')
        {
            return $this->response->array([
                    'message'     => "Please send proper data.",
                    'status_code' => 400
                ]);
        }
        else
        {

            $attendeeList = UserEvents::with('userevent_user')->with('userevent_event')->where('event_id',$TaskData->event_id)->paginate(10);


            if (count($attendeeList) > 0)
            {
                $data = array();

                foreach ($attendeeList as $key => $attendee)
                {
                    $attendee_data = '';
                    if ($TaskData->user_id != $attendee->user_id) {

                        // Register fields for all user
                        $register_field_data = Fields::where('type', 'register')->get();

                        $register_fields = array();
                        if (!empty($register_field_data[0])) {
                            foreach ($register_field_data as $key => $register_field) {
                                if ($register_field->active == 1) {
                                    $value = RoleFieldValues::select('value', 'is_privacy')->where('field_id', $register_field->id)->where('user_id', $attendee->user_id)->first();
                                    if ($value['is_privacy'] == 0) {
                                        $register_fields[$register_field->display_name] = isset($value['value']) ? $value['value'] : "";
                                    }else{
                                        $register_fields[$register_field->display_name] = "";
                                    }
                                }
                            }
                        }

                        $attendee_data = array(
                            'attendee_id'           => (int)$attendee->user_id,
                            'attendee_name'         => $attendee->userevent_user['name'],
                            'attendee_email'        => $attendee->userevent_user['email'],
                            'attendee_phone_number' => $attendee->userevent_user['phone_number'],
                            'attendee_user_avatar'  => ($attendee->userevent_user['user_avatar']) ? asset($attendee->userevent_user['user_avatar']) : "",
                            'register_fields'       => $register_fields
                        );
                        $data[] = $attendee_data;
                    }
                }

                return $this->response->array([
                    'data'         => $data,
                    'total'        => $attendeeList->total(),
                    'per_page'     => $attendeeList->perPage(),
                    'current_page' => $attendeeList->currentPage(),
                    'last_page'    => $attendeeList->lastPage(),
                    'status_code'  => 200
                ]);
            }
            else
            {
                return $this->response->array([
                    'message'     => "",
                    'status_code' => 204
                ]);
            }
        }
    }

    /**
     * Send RSSI Data into database
     *
     * Request Object :
     * {"task":"sendRssiData","taskData":{"data":[{"calibrationID":"2", "timestamp":"78975987598325", "beaconMinor":"157", "distanceFromCBP":"9.7", "RSSI":"-42", "filteredRSSI":"-40.434", "deviceType":"ios"},{"calibrationID":"3", "timestamp":"78975987598325", "beaconMinor":"157", "distanceFromCBP":"9.7", "RSSI":"-42", "filteredRSSI":"-40.434", "deviceType":"ios"}]}}
     *
     */
    public function sendRssiData($TaskData){

        if ($TaskData->data == '')
        {
            return $this->response->array([
                    'message'     => "Please send proper data.",
                    'status_code' => 400
                ]);
        }

        // Insert RSSI data
        $insert_data = array();
        foreach ($TaskData->data as $key => $rssi_data) {

            $insert_data[] = array(
                'calibration_id'    => $rssi_data->calibrationID,
                'timestamp'         => $rssi_data->timestamp,
                'beacon_minor'      => $rssi_data->beaconMinor,
                'distance_from_cbp' => $rssi_data->distanceFromCBP,
                'rssi'              => $rssi_data->RSSI,
                'filtered_rssi'     => $rssi_data->filteredRSSI,
                'rssi_session_id'   => $TaskData->rssi_session_id,
                'device_type'       => $TaskData->deviceType,
                'created_at'        =>\Carbon\Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at'        =>\Carbon\Carbon::now()->format('Y-m-d H:i:s')
            );
        }

        // Insert User field data
        RssiLog::insert($insert_data);


        return $this->response->array([
            'message'     => "Data updated successfully",
            'status_code' => 200
        ]);
    }

    public function createRssiSession($TaskData)
    {
        $insert_data= array(
            'name'              => $TaskData->name,
            'floorplan_id'      => $TaskData->floorplan_id,
            'status'            => 0
        );

        $id = RssiSession::create($insert_data)->id;

        return $this->response->array([
            'message'     => "Seesion added successfully",
            'rssi_session_id'     => $id,
            'status_code' => 200
        ]);
    }

    public function getRssiSessionList($TaskData)
    {
        $list = RssiSession::where('floorplan_id',$TaskData->floorplan_id)->get();
        $data = array();
        for($i=0;$i<count($list);$i++)
        {
            $start = RssiLog::where('rssi_session_id',$list[$i]->id)->orderBy('id','asc')->first();
            $end = RssiLog::where('rssi_session_id',$list[$i]->id)->orderBy('id','desc')->first();
            if(isset($start->timestamp))$start_date = $start->timestamp; else $start_date = '';
            if(isset($end->timestamp))$end_date = $end->timestamp; else $end_date = '';

            //calibration points
            $calibration_ids = RssiLog::leftjoin('calibrator','rssi_log.calibration_id','=','calibrator.id')->where('rssi_log.rssi_session_id',$list[$i]->id)->distinct('rssi_log.calibration_id')->get(['calibrator.name','rssi_log.calibration_id']);
            //prepare beacon data
            $beacons = array();
            for($u=0;$u<count($calibration_ids);$u++)
            {
                $beacons[$u]['data'] = RssiLog::groupBy('beacon_minor')->where('calibration_id',$calibration_ids[$u]->calibration_id)->where('rssi_session_id',$list[$i]->id)->select('beacon_minor', DB::raw('count(*) as total'))->get()->toArray();
                $beacons[$u]['name'] = $calibration_ids[$u]->name;
                $beacons[$u]['id'] = $calibration_ids[$u]->calibration_id;
            }

            $data[] = array(
                'id' => $list[$i]->id,
                'name' => $list[$i]->name,
                'status' => $list[$i]->status,
                'calibrators' => Calibrator::where('floorplan_id',$TaskData->floorplan_id)->count('id'),
                'calibrations' => RssiLog::where('rssi_session_id',$list[$i]->id)->count('id'),
                'start_time' => $start_date,
                'end_time' => $end_date,
                'beacons' => $beacons
            );
        }
        return $this->response->array([
            'data'     => $data,
            'status_code' => 200
        ]);
    }

    public function setRssiSessionActive($TaskData)
    {
        RssiSession::where('floorplan_id',$TaskData->floorplan_id)->update(array('status'=>0));
        RssiSession::where('id',$TaskData->rssi_session_id)->update(array('status'=>1));
        return $this->response->array([
            'message'     => "Session finalized successfully",
            'status_code' => 200
        ]);
    }

    /*
    *  Get Meetups Time List
    *
    * {"task":"getMeetupTime","taskData":{"user_id":"1","to_user_id":"36","event_id":"6", "date":"2017-04-13"}}
    *
    */
    public function getMeetupTime($TaskData) {
        if ($TaskData->user_id == '' || $TaskData->to_user_id == '' || $TaskData->event_id == '' || $TaskData->date == '') {
                return $this->response->array([
                        'message'     => "Please send proper data.",
                        'status_code' => 400
                    ]);
        }else{
            // User Registered Event Data
            //$user_reg_event_data = UserEvents::orWhere('user_id',$TaskData->user_id)->orWhere('event_id', $TaskData->event_id)->get();

            // Event Data
            //$event_data = Events::where('eventid',$TaskData->event_id)->get();

            // Get agenda data (Get session_id, meetup_id)
            $agenda_data = MyAgenda::orWhere('user_id',$TaskData->user_id)->orWhere('user_id',$TaskData->to_user_id)->where('event_id',$TaskData->event_id)->get();

            $data = array(
                'my_agenda_session' => array(),
                'my_agenda_meetup'  => array()
            );

            $session_ids = array();
            $meetup_ids  = array();

            foreach ($agenda_data as $agenda) {
                $session_ids[] = $agenda->session_id;
                $meetup_ids[]  = $agenda->meetup_id;
            }

            if ($session_ids) {
                // Get my agenda session  data
                foreach (array_unique($session_ids) as $key => $session_id) {
                    $session_data = Sessions::select('date', 'start_time', 'end_time')->where('id', $session_id)->where('event_id', $TaskData->event_id)->where('date', $TaskData->date)->where('status', 1)->get();

                    if ($session_data) {
                        foreach ($session_data as $session) {
                            $session_time = array(
                                'date'       => $session['date'],
                                'start_time' => $session['start_time'],
                                'end_time'   => $session['end_time']
                            );
                            $data['my_agenda_session'][] = $session_time;
                        }
                    }
                }
            }

            if ($meetup_ids) {
                foreach (array_unique($meetup_ids) as $key => $meetup_id) {
                    $meeting_data = Meetups::where('id', $meetup_id)->where('date', $TaskData->date)->where('status', 1)->get();
                    if ($meeting_data) {
                       foreach ($meeting_data as $meeting) {
                            $time = explode('-', $meeting['time']);
                            $meeting_time = array(
                                'date'       => $meeting['date'],
                                'start_time' => $time[0],
                                'end_time'   => $time[1]
                            );
                            $data['my_agenda_meetup'][] = $meeting_time;
                        }
                    }
                }
            }

            $data = array_merge($data['my_agenda_session'], $data['my_agenda_meetup']);
            if (!empty($data)) {
                return $this->response->array([
                    'data'        => $data,
                    'message'     => "Get Meetups time data successfully.",
                    'status_code' => 200
                ]);
            }else{
                return $this->response->array([
                    'message'     => "On that day all time available.",
                    'status_code' => 204
                ]);
            }
        }
    }

    /*
    *  Get Category List
    *
    * {"task":"getCategoryList","taskData":{"category_id":"1"}}
    *
    */
    public function getCategoryList($TaskData) {
        if ($TaskData->category_id == '' ) {
                return $this->response->array([
                        'message'     => "Please send proper data.",
                        'status_code' => 400
                    ]);
        }else{
            $data = array(
                'category' => array(),
                'article'  => array()
            );
            // Get parent Category Data
            /*$parent_category_data = Categories::where('id',$TaskData->category_id)->first()->toArray();

            $categories[] = array(
                'category_id'   => $parent_category_data['id'],
                'parent_id'     => $parent_category_data['parent'],
                'category_name' => $parent_category_data['title']
                );
            */
            // Get Cms Page Data
            $cms_page_datas = Cmspage::where('cat_id', $TaskData->category_id)->where('active', 1)->get()->toArray();
            $parent_cmspage_datas = array();
            foreach ($cms_page_datas as $key => $cms_page) {
                 $parent_cmspage_data = array(
                    "cms_page_id" => $cms_page['id'],
                    "category_id" => $cms_page['cat_id'],
                    "title"       => $cms_page['display_name'],
                    "description" => $cms_page['description'],
                    );
                  $parent_cmspage_datas[] = $parent_cmspage_data;
            }

            $category_data = $this->fetchCategoryTree($TaskData->category_id);

            if (isset($category_data)) {
                $categories_datas = array();
                $articles = array();
                foreach ($category_data as $ckey => $category) {
                    $categories_data = array(
                        "category_id"   => $category['id'],
                        "parent_id"     => $category['parent'],
                        "category_name" => $category['title']
                        );

                    // Get Cms Page Data
                    $cms_page_data = Cmspage::where('cat_id', $category['id'])->where('active', 1)->get()->toArray();
                    foreach ($cms_page_data as $key => $cms_page) {
                        $cmspage_data = array(
                            "cms_page_id" => $cms_page['id'],
                            "category_id" => $cms_page['cat_id'],
                            "title"       => $cms_page['display_name'],
                            "description" => $cms_page['description'],
                            );
                        $articles[] = $cmspage_data;
                    }
                    $categories_datas[] = $categories_data;
                }
                $data['article']  = $articles;
                $data['category'] = $categories_datas;
            }

            /*$category_data = array_merge($categories, $data['category']);
            $data['category'] = $category_data;*/
            $article_data  = array_merge($parent_cmspage_datas, $data['article']);
            $data['article']  = $article_data;

            if (!empty($data)) {
                return $this->response->array([
                    'data'        => $data,
                    'message'     => "Get category and CMS Page data successfully.",
                    'status_code' => 200
                ]);
            }else{
                return $this->response->array([
                    'message'     => "No Data Found.",
                    'status_code' => 204
                ]);
            }
        }
    }

    public function fetchCategoryTree($parent, $categories_tree_array = '')
    {
        if (!is_array($categories_tree_array))
        {
            $categories_tree_array = array();
        }

        /*$category_data = Categories::Where('id',$parent)->first();
        //echo "<pre/>";print_r($category_data);exit;
        if ($category_data) {
            $category[] = array(
                "id"         => $category_data->id,
                "title"      => $category_data->title,
                "parent"     => $category_data->parent,
                "status"     => $category_data->status
                );
        }*/

        $categories_data = Categories::Where('parent',$parent)->get();

        if (count($categories_data) > 0)
        {
            foreach ($categories_data as $key => $category)
            {
                $categories_tree_array[] = array(
                    "id"         => $category->id,
                    "title"      => $category->title,
                    "parent"     => $category->parent,
                    "status"     => $category->status
                    );
                $categories_tree_array = $this->fetchCategoryTree($category->id, $categories_tree_array);
            }
        }

        return $categories_tree_array;
    }
















    /*Hadoop Webservice start*/

    /*
    *   Upload user phone-book
    *
    * {"task":"uploadPhoneBook","taskData":{"user_id":"2","contacts":"9016800790,8866081516"}}
    *
    */
    public function uploadPhoneBook($TaskData){
        $user_id = $TaskData->user_id;
        if ($user_id == '') {
            return $this->response->array([
                'message'     => "Please send proper data.",
                'status_code' => 400
            ]);
        }else{

            $data = array();

            DB::connection('odbc')->statement("Delete from indolytics.phonebook where user_id = $user_id ");

            $contacts = explode(',',$TaskData->contacts);

            if(count($contacts) > 0)
            {
                foreach($contacts as $contact)
                {
                    if($contact != '' && $user_id != '')
                    {
                        $data[] = "(".$user_id.",'".$contact."')";
                    }
                }

            }
            $all_data = implode(',',$data);
            // Insert contacts For PhoneBook Data
            $insert_data = DB::connection('odbc')->statement("INSERT INTO TABLE indolytics.phonebook VALUES $all_data");

            if (!empty($insert_data)) {
                return $this->response->array([
                    'message'     => "Your Data sent to Hadoop PhoneBook Table.",
                    'status_code' => 200
                ]);
            }else{
                return $this->response->array([
                    'message'     => "",
                    'status_code' => 400
                ]);
            }
        }
    }

    /*
    *   Upload user phone-book
    *
    * {"task":"getPhoneBook","taskData":{"user_id":"2"}}
    *
    */
    public function getPhoneBook($TaskData){
        $user_id = $TaskData->user_id;
        if ($user_id == '') {
            return $this->response->array([
                'message'     => "Please send proper data.",
                'status_code' => 400
            ]);
        }else{

            $get_data = DB::connection('odbc')->select( DB::raw("SELECT * FROM indolytics.phonebook WHERE user_id = $user_id") );

            if (!empty($get_data)) {
                return $this->response->array([
                    'data'     => $get_data,
                    'status_code' => 200
                ]);
            }else{
                return $this->response->array([
                    'message'     => "",
                    'status_code' => 400
                ]);
            }
        }
    }


    public function deletePhoneBook($TaskData){
        $user_id = $TaskData->user_id;
        $all = $TaskData->all;
        if ($user_id == '' && !$all) {
            return $this->response->array([
                'message'     => "Please send proper data.",
                'status_code' => 400
            ]);
        }else{
            if($all)
            {
                $get_data = DB::connection('odbc')->statement("Delete from indolytics.phonebook");
            }
            else
            {
                $get_data = DB::connection('odbc')->statement("Delete from indolytics.phonebook WHERE user_id = $user_id");
            }
            if (!empty($get_data)) {
                return $this->response->array([
                    'data'     => "Contacts Deleted Successfully",
                    'status_code' => 200
                ]);
            }else{
                return $this->response->array([
                    'message'     => "",
                    'status_code' => 400
                ]);
            }
        }
    }

    /*
    *   Audit Track Record
    *
    * {"task":"addAuditTrackRecord","taskData":{"user_id":"2","section":"screen name/section name", "action":"login/logout/button clicks/", "time_stamp":"timestamp", "device_id":"device token/ip add", "device_type":"ios/android/web"}}
    *
    */
    public function addAuditTrackRecord($TaskData){
        $user_id = $TaskData->user_id;
        if ($TaskData->user_id == '' || $TaskData->section == '' || $TaskData->action == '' || $TaskData->time_stamp == '' || $TaskData->device_id == '' || $TaskData->device_type == '') {
            return $this->response->array([
                'message'     => "Please send proper data.",
                'status_code' => 400
            ]);
        }else{

            $data = "(".$TaskData->user_id.",'".$TaskData->section."','".$TaskData->action."','".$TaskData->time_stamp."','".$TaskData->device_id."','".$TaskData->device_type."')";

            // Insert contacts For Audit Track report
            $insert_data = DB::connection('odbc')->statement("INSERT INTO TABLE indolytics.audit_track_report VALUES $data");

            if (!empty($insert_data)) {
                return $this->response->array([
                    'message'     => "Your Data sent to Audit Track Record.",
                    'status_code' => 200
                ]);
            }else{
                return $this->response->array([
                    'message'     => "Please send proper data.",
                    'status_code' => 400
                ]);
            }
        }
    }

    /**
     * {"task":"deleteAuditTrackRecord","taskData":{"user_id":"2","all":"1"}}
     * Note : This delete all audit record
     *
     * {"task":"deleteAuditTrackRecord","taskData":{"user_id":"2","all":"0"}}
     * Note : This delete user_id 2 audit record
     */
    public function deleteAuditTrackRecord($TaskData){
        $user_id = $TaskData->user_id;
        $all     = $TaskData->all;
        if ($user_id == '' && !$all) {
            return $this->response->array([
                'message'     => "Please send proper data.",
                'status_code' => 400
            ]);
        }else{
            if($all)
            {
                $get_data = DB::connection('odbc')->statement("Delete from indolytics.audit_track_report");
            }
            else
            {
                $get_data = DB::connection('odbc')->statement("Delete from indolytics.audit_track_report WHERE user_id = $user_id");
            }
            if (!empty($get_data)) {
                return $this->response->array([
                    'data'     => "Audit Track Record Deleted Successfully",
                    'status_code' => 200
                ]);
            }else{
                return $this->response->array([
                    'message'     => "",
                    'status_code' => 400
                ]);
            }
        }
    }

    /*
    *   Get audit track record
    *
    * {"task":"getAuditTrackRecord","taskData":{"user_id":"2"}}
    *
    */
    public function getAuditTrackRecord($TaskData){
        $user_id = $TaskData->user_id;
        if ($user_id == '') {
            return $this->response->array([
                'message'     => "Please send proper data.",
                'status_code' => 400
            ]);
        }else{

            $get_data = DB::connection('odbc')->select( DB::raw("SELECT * FROM indolytics.audit_track_report WHERE user_id = $user_id") );

            if (!empty($get_data)) {
                return $this->response->array([
                    'data'     => $get_data,
                    'status_code' => 200
                ]);
            }else{
                return $this->response->array([
                    'message'     => "",
                    'status_code' => 400
                ]);
            }
        }
    }
    /*Hadoop Webservice ends*/

    public function getBeacon($TaskData)
    {
        $device_type = $TaskData->deviceType;

        $floorplan_data = Floorplan::where('id',1)->get();
        $beacon_data = Beacon::where('floorplan_id',1)->where('beacon_X','!=','')->where('beacon_X','!=','NaN')->get();
        $calibrator_data = Calibrator::where('floorplan_id',1)->get();
        $nodes_data = Nodes::where('floorplan_id',1)->get();
        $poi_data = Poi::where('floorplan_id',4)->get();
        $gateway_data = Gateway::where('floorplan_id',4)->get();
        //echo "<pre>";print_r($poi_data);exit;

        $floorplan_array = array();
        for ($i=0;$i<count($floorplan_data);$i++)
        {
            $floorplan_array[] = array(
                "floorPlanID"=> $floorplan_data[$i]->id,
                "floorPlanName"=> $floorplan_data[$i]->name,
                "floorPlanImageUrl"=> url($floorplan_data[$i]->image),
                "floorPlanActualWidth"=> $floorplan_data[$i]->width,
                "floorPlanActualHeight"=> $floorplan_data[$i]->height
            );
        }

        //rssi mode calculation code
        $raw_data_beacon = RssiLog::where('device_type',$device_type)->groupBy('beacon_minor')->distinct()->get()->toArray();
        $data = array();
        for($i=0;$i<count($raw_data_beacon);$i++)
        {
            $raw_data_distance = RssiLog::where('device_type',$device_type)->groupBy('distance_from_cbp')->get()->toArray();
            for($j=0;$j<count($raw_data_distance);$j++)
            {
                $temp_data = RssiLog::where('beacon_minor',$raw_data_beacon[$i]['beacon_minor'])->where('distance_from_cbp',$raw_data_distance[$j]['distance_from_cbp'])->lists('filtered_rssi')->toArray();
                if(count($temp_data))
                {
                    $data[$raw_data_beacon[$i]['beacon_minor']][$raw_data_distance[$j]['distance_from_cbp']] = $temp_data;
                }
            }
        }

        $final_data = array();
        foreach($data as $key=>$value)
        {
            foreach($value as $ikey=>$ivalue)
            {
                $temp_1 = array();
                for($o=0;$o<count($ivalue);$o++)
                {
                    $temp_1[] =  (int)round($ivalue[$o]);
                }
                $temp_2 = array();
                $temp_2 = array_count_values($temp_1);
                arsort($temp_2);
                $final_data[$key][$ikey] = array_values(array_flip($temp_2))[0];
            }
        }

        $beacons_array = array();
        for ($i=0;$i<count($beacon_data);$i++)
        {
            //rssi mode code
            $fingerprint_array = array();
            if(isset($final_data[$beacon_data[$i]->minor_id]))
            {
                foreach($final_data[$beacon_data[$i]->minor_id] as $key=>$value)
                {
                    $fingerprint_array[] = array('distance'=>$key,'rssi_mode'=>$value);
                }
            }

            $beacons_array[] = array(
                "floorPlanID"=> $beacon_data[$i]->floorplan_id,
                "beaconName"=> $beacon_data[$i]->name,
                "beaconType"=> $beacon_data[$i]->type,
                "beaconID"=> $beacon_data[$i]->id,
                "beaconMajorID"=> $beacon_data[$i]->major_id,
                "beaconMinorID"=> $beacon_data[$i]->minor_id,
                "beaconUUID"=> $beacon_data[$i]->uuid,
                "beaconLocationX"=> $beacon_data[$i]->display_X,
                "beaconLocationY"=> $beacon_data[$i]->display_Y,
                "fingerprint" => $fingerprint_array
            );
        }
        $calibrator_array = array();
        for ($i=0;$i<count($calibrator_data);$i++)
        {
            $calibrator_array[] = array(
                "floorPlanID"=> $calibrator_data[$i]->floorplan_id,
                "beaconName"=> $calibrator_data[$i]->name,
                "calibratorID"=> $calibrator_data[$i]->id,
                "beaconLocationX"=> $calibrator_data[$i]->display_X,
                "beaconLocationY"=> $calibrator_data[$i]->display_Y,
                "beaconType"=> "spot",
                "sectionName"=> $calibrator_data[$i]->name,
                "beaconMajorID"=> "1008",
                "beaconMinorID"=> "6",
                "beaconUUID"=> "fda50693-a4e2-4fb1-afcf-c6eb07647825",
                "NodeID"=> 10,
                "IsChild"=> 0
            );
        }

        $nodes_array = array();
        $pathnodes_array = array();
        for ($i=0;$i<count($nodes_data);$i++)
        {
            $nodes_array[] = array(
                "nodeID"=> $nodes_data[$i]->id,
                "floorPlanID"=> $nodes_data[$i]->floorplan_id,
                "nodeName"=> $nodes_data[$i]->name,
                "nodeType"=> $nodes_data[$i]->type,
                "nodeLocationX"=> $nodes_data[$i]->display_X,
                "nodeLocationY"=> $nodes_data[$i]->display_Y
            );

            //code for path nodes
            $connected_nodes = DB::table('node_relation')->where('node_id',$nodes_data[$i]->id)->get();
            $connectionIDs = '';
            $relationArray = array();
            if(count($connected_nodes) > 0) {
                foreach ($connected_nodes as $key => $relation) {
                    $relationArray[] = $relation->rel_node_id.':'.$relation->distance;
                }
            }
            $connectionIDs = implode(',', $relationArray);
            $pathnodes_array[] = array(
                'floorPlanID'   => $nodes_data[$i]->floorplan_id,
                'nodeName'      => $nodes_data[$i]->name,
                'nodeType'      => $nodes_data[$i]->type,
                'nodeID'        => $nodes_data[$i]->id,
                'nodeLocationX' => $nodes_data[$i]->display_X,
                'nodeLocationY' => $nodes_data[$i]->display_Y,
                'connectionIDs' => $connectionIDs
            );
        }

        $spot_beacon_array = array();
        for($i=0;$i<count($poi_data);$i++)
        {
            if(!array_key_exists($poi_data[$i]->area, $spot_beacon_array)){
                $spot_beacon_array[$poi_data[$i]->area] = array(
                    "floorPlanID"=> $poi_data[$i]->floorplan_id,
                    "sectionName"=> $poi_data[$i]->area,
                    "NodeID"=> $poi_data[$i]->node_id,
                );
            }

            $spot_beacon_array[$poi_data[$i]->area]['location'][] = array("LocationX"=> $poi_data[$i]->poi_X, "LocationY"=> $poi_data[$i]->poi_Y, "DisplayX"=> $poi_data[$i]->display_X, "DisplayY"=> $poi_data[$i]->display_Y, "poi_id"=> $poi_data[$i]->id);
        }

        $final_array['floorPlans'] = $floorplan_array;
        $final_array['beacons'] = $beacons_array;
        $final_array['calibrator'] = $calibrator_array;
        $final_array['nodes'] = $nodes_array;
        $final_array['pathNodes'] = $pathnodes_array;
        $final_array['spottedBeacons'] = array_values($spot_beacon_array);
        $final_array['gateway'] = $gateway_data;
        return $this->response->array([
            'data'     => $final_array,
            'status_code' => 200
        ]);
    }

}