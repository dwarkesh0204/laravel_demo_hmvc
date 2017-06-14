<?php

namespace App\Modules\Visitors\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Visitors\Models\Visitorhadoop;

class VisitorHadoopController extends Controller
{
    public function __construct(){
        $this->VisitorhadoopModel = new Visitorhadoop();
    }

    public function visitor_log(Request $request)
    {
        $postdata = file_get_contents('php://input');
        $split = explode(':', $postdata,2); //split from : with 2 limited partitions
        //$file = public_path('request.log');
        //\File::put($file, $postdata);
        $deviceId = $split[0];
        $data_arr =  explode("|",$split[1]);//second partition all data from the device id
        return $this->VisitorhadoopModel->SetLog($deviceId,$data_arr);
    }
}
