<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Projects;
use DB;
use Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;

class ProjectsController extends Controller
{

    public function __construct() {
        $this->middleware('auth'/*, ['except' => ['index','show']]*/);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user_id = \Auth::user()->id;
        $items = Projects::where('user_id',$user_id)->where('type','<>','bm')->get();
        return view('projects.index',compact('items'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user_id = \Auth::user()->id;
        $secret = $this->unique_secret();
        return view('projects.create',compact('user_id','secret'));
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
            'title' => 'required',
            'type' => 'required'
        ]);
        $project_insert = Projects::create($request->all())->id;
        $db_response_project = $this->prepare_database($request->secret,$request->type);
        Projects::where('id',$project_insert)->update($db_response_project);
        return redirect()->route('projects.index')->with('flash_message','Project created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item  = Projects::where('id', $id)->first();
        $user_id = \Auth::user()->id;
        return view('projects.edit',compact('item','user_id'));
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
            'title' => 'required',
            'type' => 'required'
        ]);
        $input = $request->all();
        Projects::find($id)->update($input);
        return redirect()->route('projects.index')->with('flash_message','project updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $Input      = $request->all();
        $catIDs     = $Input['catIDs'];
        $catIDArray = explode(',', $catIDs);

        foreach ($catIDArray as $key => $catId)
        {
            Projects::find($catId)->delete();
        }
        return redirect()->route('projects.index')->with('flash_message','Projects deleted successfully');
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
        $Projects    = Projects::find($id);

        if ($statusMode == 0){
            $Projects->status = 0;
        }else{
            $Projects->status = 1;
        }
        $Projects->save();

        return redirect()->route('projects.index')->with('flash_message','Status changed successfully');
    }

    public function unique_secret($l = 8) {
        return strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, $l));
    }

    public function prepare_database($secret,$type)
    {
        //create database
        // We will use the `statement` method
        $org_password = str_random('10');
        $password = \Crypt::encrypt($org_password);
        $schemaName = 'saas_'.\Auth::user()->id.'_'.$secret.'_'.$type;
        DB::connection('mysql')->statement("CREATE USER '".$secret."'@'localhost' IDENTIFIED BY '".$org_password."';");
        DB::connection('mysql')->statement("GRANT ALL ON ".$schemaName.".* TO '".$secret."'@'localhost' ");
        DB::connection('mysql')->statement('create database '.$schemaName.';');


        Config::set('database.connections.'.$schemaName, array(
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => $schemaName,
            'username'  => $secret,
            'password'  => $org_password,
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ));

        switch ($type)
        {
            case 'bm':
                //create migration for database
                Artisan::call('migrate', ['--path' => "app/Modules/Beaconmanager/Database/Migrations" ,'--database' => $schemaName ]);
            break;
            case 'ips':
                //create migration for database
                Artisan::call('migrate', ['--path' => "app/Modules/Ips/Database/Migrations" ,'--database' => $schemaName ]);
                break;
            case 'attendance':
                //create database for hadoop
                DB::connection('odbc')->statement("Create database $schemaName");
                DB::connection('odbc')->statement("CREATE TABLE ".$schemaName.".iot_logs (mac_address varchar(20),device_id STRING,minor_id varchar(5) ,major_id varchar(5), logged_timestamp string,rssi varchar(5),factory_id varchar(25),ibeacon_id string,measured_power varchar(10),type varchar(20),lecture_id INT,course_name string,semester_name string,class_name string,subject_name string,starttime string,endtime  string,course_id int,semester_id int,class_id int,subject_id int,teacher_id int,teacher_app varchar(1)) CLUSTERED BY (device_id) into 2 buckets ROW FORMAT DELIMITED FIELDS TERMINATED BY ',' STORED as orc tblproperties('transactional'='true');");
                //create migration for database
                Artisan::call('migrate', ['--path' => "app/Modules/Attendance/Database/Migrations" ,'--database' => $schemaName ]);
                break;
            case 'events':
                //create migration for database
                Artisan::call('migrate', ['--path' => "app/Modules/Events/Database/Migrations" ,'--database' => $schemaName ]);
                DB::connection('odbc')->statement("Create database $schemaName");
                DB::connection('odbc')->statement("CREATE TABLE ".$schemaName.".phonebook (user_id INT,contact string) CLUSTERED BY (user_id) into 1 buckets ROW FORMAT DELIMITED FIELDS TERMINATED BY ',' STORED as orc tblproperties('transactional'='true');");
                DB::connection('odbc')->statement("CREATE TABLE ".$schemaName.".audit_track_record (user_id INT,section string,action string,time_stamp string,device_id string,device_type string) CLUSTERED BY (device_id) into 1 buckets ROW FORMAT DELIMITED FIELDS TERMINATED BY ',' STORED as orc tblproperties('transactional'='true');");
                break;
            case 'storesolution':
                //create migration for database
                Artisan::call('migrate', ['--path' => "app/Modules/Storesolution/Database/Migrations" ,'--database' => $schemaName ]);
                break;
            case 'visitors':
                //create database for hadoop
               // DB::connection('odbc')->statement("Create database $schemaName");
               // DB::connection('odbc')->statement("CREATE TABLE ".$schemaName.".visitors_logs (mac_address varchar(20),device_id STRING,minor_id varchar(5) ,major_id varchar(5), logged_timestamp string,rssi varchar(5),factory_id varchar(25),ibeacon_id string,measured_power varchar(10),type varchar(20),visitor_id INT,visitor_name string,card_id int) CLUSTERED BY (device_id) into 2 buckets ROW FORMAT DELIMITED FIELDS TERMINATED BY ',' STORED as orc tblproperties('transactional'='true');");
                //create migration for database
                Artisan::call('migrate', ['--path' => "app/Modules/Visitors/Database/Migrations" ,'--database' => $schemaName ]);
                break;

            case 'proximity':
                //create database for hadoop
                //DB::connection('odbc')->statement("Create database $schemaName");
                //DB::connection('odbc')->statement("CREATE TABLE ".$schemaName.".visitors_logs (mac_address varchar(20),device_id STRING,minor_id varchar(5) ,major_id varchar(5), logged_timestamp string,rssi varchar(5),factory_id varchar(25),ibeacon_id string,measured_power varchar(10),type varchar(20),visitor_id INT,visitor_name string,card_id int) CLUSTERED BY (device_id) into 2 buckets ROW FORMAT DELIMITED FIELDS TERMINATED BY ',' STORED as orc tblproperties('transactional'='true');");
                //create migration for database
                Artisan::call('migrate', ['--path' => "app/Modules/Proximity/Database/Migrations" ,'--database' => $schemaName ]);
                break;
        }
        return array('db'=>$schemaName,'db_user'=>$secret,'db_password'=>$password);
    }

    public function access(Request $request)
    {
        $data = Projects::where('id',$request->id)->first();
        \Session::put('project_conn_id',$request->id);

        switch ($data->type)
        {
            case 'bm':
                //create migration for database
                return redirect('beaconmanager/manufacture');
                break;
            case 'ips':
                //create migration for database
                return redirect('ips/floorplan');
                break;
            case 'attendance':
                //create migration for database
                return redirect('attendance/subject');
                break;
            case 'events':
                //create migration for database
                return redirect('events/menu');
                break;
            case 'storesolution':
                //create migration for database
                return redirect('storesolution/stores');
                break;
            case 'visitors':
                //create migration for database
                return redirect('visitors/visitors');
                break;
            case 'proximity':
                //create migration for database
                return redirect('proximity/campaignManager');
                break;
        }
    }
    public function sessionError()
    {
        $project_conn_id = Session::get('project_conn_id');
        $data = Projects::where('id',$project_conn_id)->select('title')->first();
        return view('projects.session_error',compact('data'));
    }
}
