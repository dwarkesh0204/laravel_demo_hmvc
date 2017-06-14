<?php

namespace App\Modules\Proximity\Http\Controllers;


use Illuminate\Http\Request;
use App\Modules\Proximity\Http\Requests;
use App\Modules\Proximity\Models\AdsManager;
use App\Modules\Proximity\Models\CampaignManager;
use App\Modules\Beaconmanager\Models\Beacon;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;
use PulkitJalan\Google\Facades\Google;

class AdsManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = AdsManager::orderBy('id','DESC')->paginate();

        foreach ($items as $key => $value)
        {
            if($value['campaign_manager_id'])
            {
                $campaign_manager               = CampaignManager::select('title')->where('id', $value['campaign_manager_id'])->get();
                $campaign_manager_name          = json_decode($campaign_manager);
                $value['campaign_manager_name'] = $campaign_manager_name[0]->title;
            }
        }

        $campaign_managers = CampaignManager::get();

        return view('proximity::adsManager.index',compact('items', 'campaign_managers'));
    }

    public function AdsManagerData(Request $request)
    {
        $query = AdsManager::select('ads_managers.id','ads_managers.campaign_manager_id','ads_managers.title','ads_managers.description','ads_managers.link','ads_managers.created_at','ads_managers.updated_at','ads_managers.status','campaign_managers.title as campaign_manager_name');
        $provinces = $query->leftjoin('campaign_managers','campaign_managers.id','=','ads_managers.campaign_manager_id');


        //$provinces = AdsManager::select('*');
        return Datatables::of($provinces)->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $camapignManagers = CampaignManager::pluck('title','id')->toArray();
        $beacons = Beacon::pluck('name','id')->toArray();

        return view('proximity::adsManager.create',compact('camapignManagers','beacons'));
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
            'title'               => 'required',
            'beacon_id' => 'required'
        ]);

        $input = $request->all();
        //$adsManager = AdsManager::create($input);

        $input                           = $request->all();
        $AdsManager                      = new AdsManager();
        $AdsManager->title               =  $input['title'];
        $AdsManager->description         =  $input['description'];
        //$AdsManager->campaign_manager_id =  $input['campaign_manager_id'];
        $AdsManager->beacon_id           =  $input['beacon_id'];
        $AdsManager->link                =  $input['link'];
        $AdsManager->start_date          =  $input['start_date'];
        $AdsManager->end_date            =  $input['end_date'];
        $AdsManager->start_time          =  $input['start_time'];
        $AdsManager->end_time            =  $input['end_time'];
        $AdsManager->link                =  $input['link'];
        $AdsManager->status              =  $input['status'];
        $AdsManager->save();
        $adsManagerId = $AdsManager->getAttribute('id');

        $beaconData      = Beacon::find($input['beacon_id']);
        $beconName       = $beaconData->beaconName;

        $listAttachments = $this->getListOfAttachments($beconName);

        $attachmentName  = $listAttachments['attachments'][0]->attachmentName;

        if($attachmentName)
        {
          $removedAttachment  = $this->DeleteAttachment($attachmentName);
        }

        $attachment = $this->addAttachmentToBeacons($input);

        if($request->hasFile('image')) {
            $file = $request->file('image');

            // Create Directory
            Storage::disk('upload')->makeDirectory('AdsManager/'.$adsManagerId);

            $storage = public_path()."/uploads/AdsManager/".$adsManagerId;

            // Create A FileName From Uploaded File.
            $fileName = time().$request->file('image')->getClientOriginalName();

            // Remove Spaces In The Filename And Convert To LowerCase.
            $fileName = str_replace(' ', '', strtolower($fileName));

            // Move The File To Storage Directory
            if($request->file('image')->move($storage, $fileName))
            {
                $FileNameStore     = "/uploads/AdsManager/".$adsManagerId."/".$fileName;
                $adsManager        = AdsManager::find($adsManagerId);
                $adsManager->image = $FileNameStore;
                $adsManager->save();
            }
        }

        /*// Store in audit
        $auditData                = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'adsManager';
        $auditData['action']      = 'adsManager.create';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);*/

        return redirect()->route('adsManager.index')->with('success','Ads Manager created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item             = AdsManager::find($id);
        $camapignManagers = CampaignManager::pluck('title','id')->toArray();

        $beacons = Beacon::pluck('name','id')->toArray();



        return view('proximity::adsManager.edit',compact('item','camapignManagers','beacons'));
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
            'title'               => 'required',
            'beacon_id' => 'required'
        ]);

        $input = $request->all();
        AdsManager::find($id)->update($input);


        $beaconData      = Beacon::find($input['beacon_id']);
        $beconName       = $beaconData->beaconName;

        $listAttachments = $this->getListOfAttachments($beconName);

        $attachmentName  = $listAttachments['attachments'][0]->attachmentName;

        if($attachmentName)
        {
          $removedAttachment  = $this->DeleteAttachment($attachmentName);
        }

        $attachment = $this->addAttachmentToBeacons($input);

        if($request->hasFile('image')) {
            $file = $request->file('image');

            // Create Directory
            Storage::disk('upload')->makeDirectory('AdsManager/'.$id);

            $storage = public_path()."/uploads/AdsManager/".$id;

            // Create A FileName From Uploaded File.
            $fileName = time().$request->file('image')->getClientOriginalName();

            // Remove Spaces In The Filename And Convert To LowerCase.
            $fileName = str_replace(' ', '', strtolower($fileName));

            // Move The File To Storage Directory
            if($request->file('image')->move($storage,$fileName)){

                $FileNameStore     = "/uploads/AdsManager/".$id."/".$fileName;
                $adsManager        = AdsManager::find($id);
                $adsManager->image = $FileNameStore;
                $adsManager->save();
            }
        }

        return redirect()->route('adsManager.index')->with('flash_message','Ads Manager updated successfully');
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

        $adsManagerIDs     = $Input['adsManagerIDs'];
        $adsManagerIDArray = explode(',', $adsManagerIDs);

        foreach ($adsManagerIDArray as $key => $adsManagerID)
        {
            $adsManager = AdsManager::find($adsManagerID);

            $beaconData      = Beacon::find($adsManager->beacon_id);
            $beconName       = $beaconData->beaconName;

            $listAttachments = $this->getListOfAttachments($beconName);

            foreach ($listAttachments as $key => $listAttachment)
            {
                $attachmentName  = $listAttachment->attachmentName;

                if($attachmentName)
                {
                  $removedAttachment  = $this->DeleteAttachment($attachmentName);
                }
            }

            $adsManager->delete();
        }

        return redirect()->route('adsManager.index')->with('flash_message','Ads Manager deleted successfully');
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
        $AdsManager = AdsManager::find($id);

        if ($statusMode == 0){
            $AdsManager->status = 0;
        }else{
            $AdsManager->status = 1;
        }
        $AdsManager->save();

        return redirect()->route('adsManager.index')->with('flash_message','Status changed successfully');
    }

    /**
     * Delete Image.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteImage(Request $request)
    {
        $Input        = $request->all();
        $adsManagerId = $Input['id'];
        $adsManager   = AdsManager::find($adsManagerId);

        $imagePath = $adsManager['image'];
        unlink(public_path().$imagePath);
        $adsManager['image'] = "";
        $adsManager->save();

        return redirect()->back()->with('flash_message','Image deleted successfully');
    }

    public function addAttachmentToBeacons($adData)
    {
        $googleClient = Google::getClient();
        $googleClient->useApplicationDefaultCredentials();

        $Proximitybeacon = Google::make('proximitybeacon');

        $Proximitybeacon_BeaconAttachment = Google::make('Proximitybeacon_BeaconAttachment');

        //$dataStr = '{"title": "IGI is Hackable. Help to Resolve.","url": "https://www.indolytics.com/igi.html"}';
        $dataStr = '{
          "title": "'.$adData['title'].'",
          "url": "'.$adData['link'].'",
          "targeting":[
            {
              "startDate": "'.$adData['start_date'].'",
              "endDate": "'.$adData['end_date'].'",
              "startTimeOfDay": "'.$adData['start_time'].'",
              "endTimeOfDay": "'.$adData['end_time'].'",
              "anyOfDaysOfWeek": [1, 2, 3, 4, 5, 6, 7]
            }
          ]
        }';

       $data = base64_encode($dataStr);

        $beaconAttachmentData = new $Proximitybeacon_BeaconAttachment(array(
            "namespacedType" => "com.google.nearby/en",
            "data" => $data
        ));
        $beaconData = Beacon::find($adData['beacon_id']);
        $beconName = $beaconData->beaconName;
        $storebeaconAds   = $Proximitybeacon->beacons_attachments->create($beconName, $beaconAttachmentData);

        return $storebeaconAds;
    }

    public function getListOfAttachments($beconName)
    {

        $googleClient = Google::getClient();
        $googleClient->useApplicationDefaultCredentials();

        $Proximitybeacon = Google::make('proximitybeacon');

        $Proximitybeacon_BeaconAttachment = Google::make('Proximitybeacon_BeaconAttachment');

        //$beconName = $beaconData->beaconName;
        $listAttachments   = $Proximitybeacon->beacons_attachments->listBeaconsAttachments($beconName);

        return $listAttachments;
    }

    public function DeleteAttachment($attachmentName)
    {
        $googleClient = Google::getClient();
        $googleClient->useApplicationDefaultCredentials();

        $Proximitybeacon = Google::make('proximitybeacon');

        $Proximitybeacon_BeaconAttachment = Google::make('Proximitybeacon_BeaconAttachment');

        //$beconName = $beaconData->beaconName;

        $removedAttachment   = $Proximitybeacon->beacons_attachments->delete($attachmentName);

        return $removedAttachment;


    }
}
