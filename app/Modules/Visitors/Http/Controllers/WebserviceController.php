<?php

namespace App\Modules\Visitors\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Visitors\Models\Webservice;

class WebserviceController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){
        $this->WebServiceModel = new WebService();
    }

    /**
     * Use to get Request Object
     */
    public function index(Request $request){

        $Data     = json_decode(stripslashes(htmlspecialchars_decode(urldecode($request->reqObject))));
        $Task     = $Data->task;
        $TaskData = $Data->taskData;

        if($Task != ""){
            switch ($Task) {
                case 'register':
                    return $this->WebServiceModel->register($TaskData);
                    break;
                case 'attendance':
                    return $this->WebServiceModel->attendance($TaskData);
                    break;
                case 'notifications':
                    return $this->WebServiceModel->notifications($TaskData);
                    break;
                case 'register_params':
                    return $this->WebServiceModel->registerationparams($TaskData);
                    break;
                /*Hadoop Webservices End*/
            }
        }else{
            echo "No task Found";
        }
    }
}