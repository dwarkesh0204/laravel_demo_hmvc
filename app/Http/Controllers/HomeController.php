<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user_id = \Auth::User()->id;
        $widgets = DB::table('manage_widgets')->select('*')->where('user_id',$user_id)->orderBy('ordering')->get();
        return view('home', ['widgets' => $widgets]);
    }

    public function ajaxUpdateWidgetOrdering(Request $request)
    {
        $ordering_data = $request->get('ordering_data');
        if ($ordering_data != '') {
            $ordering_data = explode(",", $ordering_data);
            if (count($ordering_data) > 0) {
                for ($i=0; $i < count($ordering_data); $i++) {
                    $item = explode("=", $ordering_data[$i]);
                    DB::table('manage_widgets')
                        ->where('name', $item[0])
                        ->update(['ordering' => $item[1]]);
                }
            }
        }
        exit();
    }

    /**
     * Show the application dashboard all visitors.
     *
     * @return \Illuminate\Http\Response
     */
    public function visitors()
    {
        return view('dashboard.visitors');
    }
    public function getTestLog()
    {
        $get_data = DB::connection('odbc')->select( DB::raw("SELECT * FROM test_logs.test_iot_logs order by logged_timestamp desc ") );
        $JsonArray['logs'] = $get_data;
        dd($get_data);
        header("content-type: application/json");
        echo json_encode($JsonArray);exit;
    }
}
