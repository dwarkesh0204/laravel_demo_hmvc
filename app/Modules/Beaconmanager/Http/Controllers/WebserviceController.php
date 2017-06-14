<?php

namespace App\Modules\Beaconmanager\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Beaconmanager\Models\Webservice;

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
                case 'postBeacon':
                    return $this->WebServiceModel->postBeacon($TaskData);
                    break;
                case 'getBeacon':
                    return $this->WebServiceModel->getBeacon($TaskData);
                    break;
                case 'getIot':
                    return $this->WebServiceModel->getIot($TaskData);
                    break;
            }
        }else{
            echo "No task Found";
        }
    }
}