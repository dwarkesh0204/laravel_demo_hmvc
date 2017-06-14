<?php

namespace App\Modules\Attendance\Http\Controllers;

use App\Modules\Attendance\Models\Notifications;
use App\Modules\Attendance\Models\NotificationsStudent;
use App\Modules\Attendance\Models\Student;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use Yajra\Datatables\Facades\Datatables;

class NotificationsController extends Controller
{

    public function __construct(){
        $this->NotificationsModel = new Notifications();
    }

    public function index(Request $request)
    {
        $notifications = Notifications::orderBy('id','DESC')->get();
        return view('attendance::notifications.index',compact('notifications'))->with('i', ($request->input('page', 1) - 1) * 5);
    }

    public function NotificationsData(Request $request)
    {
        $provinces = Notifications::select('*');
        return Datatables::of($provinces)->make(true);
    }

    public function create()
    {
        $students = Student::pluck('name','id')->toArray();
        return view('attendance::notifications.create',compact('students'));
    }

    public function store(Request $request)
    {

        $this->validate($request, [
            'subject'      => 'required',
            'notification' => 'required',
            'students'     => 'required',
        ]);

        $input = array(
            'subject'      => $request->subject,
            'notification' => $request->notification,
            'created_at'   => Carbon::now()
        );

        //Push Notifications
        $push_notf_message = $request->notification;
        $push_notf_data    = array(
                'type'    => 'student',
                'details' => array(
                'subject' => $request->notification,
             )
        );

        $notification = Notifications::insertGetId($input);
        $StudentData = Student::whereIn('id',$request->students)->get();
        foreach($StudentData as $key=>$deviceData)
        {
            if($deviceData->device_type == 'iphone' || $deviceData->device_type == 'ios'){
                $push_notf_options = array(
                    'device_token' => $deviceData->device_token,
                    'aps'          => $push_notf_data
                );
                $push_notf_options['aps']['alert'] = $push_notf_message;
                $this->NotificationsModel->sendIphonePushNotification($push_notf_options);
            }

            if($deviceData->device_type == 'android'){
                $push_notf_options = array(
                    'registration_ids' => $deviceData->device_token,
                    'data'             => $push_notf_data
                );
                $push_notf_options['data']['message'] = $push_notf_message;
                $this->NotificationsModel->sendAndroidPushNotification($push_notf_options);
            }

            $insert_arr['student_id']      = $deviceData->id;
            $insert_arr['minor_id']        = $deviceData->minor_id;
            $insert_arr['major_id']        = $deviceData->major_id;
            $insert_arr['notification_id'] = $notification;
            $insert_allarr[] = $insert_arr;
        }
        NotificationsStudent::insert($insert_allarr);

        return redirect()->route('notifications.index')->with('success','Notification created successfully');
    }
}
