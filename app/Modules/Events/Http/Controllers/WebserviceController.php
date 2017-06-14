<?php

namespace App\Modules\Events\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Events\Models\Webservice;

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
                case 'login':
                    return $this->WebServiceModel->login($TaskData);
                    break;
                case 'logout':
                    return $this->WebServiceModel->logout($TaskData);
                    break;
                case 'getMenu':
                    return $this->WebServiceModel->getMenu($TaskData);
                    break;
                case 'register':
                    $userAvatar = "";
                    if ($request->hasFile('user_avatar')) {
                        $userAvatar = $request->file('user_avatar');
                    }
                    return $this->WebServiceModel->register($TaskData, $userAvatar);
                    break;
                case 'forgotPassword':
                    return $this->WebServiceModel->forgotPassword($TaskData);
                    break;
                case 'userInfo':
                    return $this->WebServiceModel->userInfo($TaskData);
                    break;
                case 'userEdit':
                    return $this->WebServiceModel->userEdit($TaskData);
                    break;
                case 'eventList':
                    return $this->WebServiceModel->eventList($TaskData);
                    break;
                case 'eventDetail':
                    return $this->WebServiceModel->eventDetail($TaskData);
                    break;
                case 'sessionsList':
                    return $this->WebServiceModel->sessionsList($TaskData);
                    break;
                case 'getPollingList':
                    return $this->WebServiceModel->getPollingList($TaskData);
                    break;
                case 'saveUserPollingAnswer':
                    return $this->WebServiceModel->saveUserPollingAnswer($TaskData);
                    break;
                case 'saveFeedback':
                    return $this->WebServiceModel->saveFeedback($TaskData);
                    break;
                case 'saveAskQuestion':
                    return $this->WebServiceModel->saveAskQuestion($TaskData);
                    break;
                case 'getMenuItem':
                    return $this->WebServiceModel->getMenuItem($TaskData);
                    break;
                case 'sendUserRequestForSession':
                    return $this->WebServiceModel->sendUserRequestForSession($TaskData);
                    break;
                case 'sendUserRequestForEvent':
                    return $this->WebServiceModel->sendUserRequestForEvent($TaskData);
                    break;
				case 'getTicketList':
                    return $this->WebServiceModel->getTicketList($TaskData);
                    break;
				case 'addToMyAgenda':
                    return $this->WebServiceModel->addToMyAgenda($TaskData);
                    break;
                case 'getAttendeeList':
                    return $this->WebServiceModel->getAttendeeList($TaskData);
                    break;
                case 'sendMeetUpRequest':
                    return $this->WebServiceModel->sendMeetUpRequest($TaskData);
                    break;
                case 'sendMessages':
                    return $this->WebServiceModel->sendMessages($TaskData);
                    break;





                /*Hadoop Webservices Start*/
                case 'uploadPhoneBook':
                    return $this->WebServiceModel->uploadPhoneBook($TaskData);
                    break;
                case 'getPhoneBook':
                    return $this->WebServiceModel->getPhoneBook($TaskData);
                    break;
                case 'deletePhoneBook':
                    return $this->WebServiceModel->deletePhoneBook($TaskData);
                    break;
                case 'auditTrackRecord':
                    return $this->WebServiceModel->auditTrackRecord($TaskData);
                    break;
                /*Hadoop Webservices End*/


            }
        }else{
            echo "No task Found";
        }
    }
}
