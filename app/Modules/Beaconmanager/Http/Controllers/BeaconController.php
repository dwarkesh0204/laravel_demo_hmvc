<?php

namespace App\Modules\Beaconmanager\Http\Controllers;

use App\Modules\Beaconmanager\Models\Beacon;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Http\Controllers\Controller;

use Yajra\Datatables\Facades\Datatables;

use Carbon\Carbon;

use PulkitJalan\Google\Facades\Google;

class BeaconController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $items = Beacon::where('device_type','')->orderBy('id','ASC')->paginate();




        return view('beaconmanager::beacon.index',compact('items'));
    }

    public function BeaconData(Request $request)
    {
        $provinces = Beacon::select('*')->where('device_type','');

        return Datatables::of($provinces)->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user_id = \Auth::user()->id;

        return view('beaconmanager::beacon.create',compact('user_id'));
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
            'name' => 'required',
            //'macid'=>'required'
        ]);

        $beacon = new Beacon();
        $beacon->name       = $request->name;
        $beacon->type       = $request->type;
        $beacon->macid      = $request->macid;
        $beacon->major_id   = $request->major_id;
        $beacon->minor_id   = $request->minor_id;
        $beacon->beacon_X   = $request->beacon_X;
        $beacon->beacon_Y   = $request->beacon_Y;
        $beacon->uuid       = $request->uuid;
        $beacon->location   = $request->location;
        $beacon->placeId    = $request->placeId;
        $beacon->created_at = Carbon::now();
        $beacon->updated_at = Carbon::now();
        $beacon->save();

        $lastId = $beacon->getAttribute('id');

        $registerDatas = $this->registerBeacons($request);

        $beaconName = $registerDatas->beaconName;
        $Beacon              = Beacon::find($lastId);
        $Beacon->beaconName  = $beaconName;
        $Beacon->advertiseId = $registerDatas['advertisedId']['id'];
        $Beacon->save();

        return redirect()->route('beacon.index')->with('flash_message','Beacon created successfully');
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
        $item = Beacon::find($id);
        $user_id = \Auth::user()->id;

        return view('beaconmanager::beacon.edit',compact('item','user_id'));
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
            'name' => 'required'
        ]);

        $input = $request->all();

        Beacon::find($id)->update($input);

        $this->updateBeacons($input,$id);

        return redirect()->route('beacon.index')->with('flash_message','Beacon updated successfully');
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
            $beaconData = Beacon::find($catId);
            $bName = $beaconData->beaconName;
            Beacon::find($catId)->delete();
            $delBeaconData = $this->deleteBeacons($bName);
        }

        return redirect()->route('beacon.index')->with('flash_message','Beacon deleted successfully');
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
        $Beacon    = Beacon::find($id);

        if ($statusMode == 0){
            $Beacon->status = 0;
        }else{
            $Beacon->status = 1;
        }
        $Beacon->save();

        return redirect()->route('beacon.index')->with('flash_message','Status changed successfully');
    }

    public function updateBeacons($data, $id)
    {
        $googleClient = Google::getClient();
        $googleClient->useApplicationDefaultCredentials();

        $Proximitybeacon = Google::make('proximitybeacon');

        // update beacon data post body...
        $Proximitybeacon_Beacon = Google::make('Proximitybeacon_Beacon');

        $beaconUpdateData = new $Proximitybeacon_Beacon(array(
            "advertisedId" => array(
                "type" => "IBEACON",
                "id" => $data['advertiseId']
            ),
            "status" => "ACTIVE",
            "placeId" => $data['placeId'],
            "latLng" => array(
                "latitude" => $data['beacon_X'],
                "longitude" => $data['beacon_Y']
            ),
            "expectedStability" => "STABLE",
            "description" => $data['name']
        ));

        $beaconName = $data['beaconName'];

        $updateBeaconData = $Proximitybeacon->beacons->update($beaconName, $beaconUpdateData);

        return $updateBeaconData;
    }

    public function deleteBeacons($bName)
    {
        $googleClient = Google::getClient();
        $googleClient->useApplicationDefaultCredentials();

        $Proximitybeacon = Google::make('proximitybeacon');

        $deleteBeacon = $Proximitybeacon->beacons->delete($bName);

        return $deleteBeacon;
    }

    public function registerBeacons($datas)
    {
        $googleClient = Google::getClient();
        $googleClient->useApplicationDefaultCredentials();

        $Proximitybeacon = Google::make('proximitybeacon');

        $Proximitybeacon_Beacon = Google::make('Proximitybeacon_Beacon');

        // beacon 1600 1008 = advertieId = "/aUGk6TiT7Gvz8brB2R4JRAIWVA="
        // TSPL17 0596 1008 = advertieId = "/aUGk6TiT7Gvz8brB2R4JRAIUA=="

        $advertiseId = $this->getAdvertiseId($datas);

        $addbeaconData = new $Proximitybeacon_Beacon(array(
            "advertisedId" => array(
                "type" => strtoupper($datas->type),
                "id" => $advertiseId
            ),
            "status" => "ACTIVE",
            "placeId" => $datas->placeId,
            "latLng" => array(
                "latitude" => $datas->beacon_X,
                "longitude" => $datas->beacon_Y
            ),
            "expectedStability" => "STABLE",
            "description" => $datas->name
        ));

        $registerBeacon = $Proximitybeacon->beacons->register($addbeaconData);

        return $registerBeacon;
    }

    public function getAdvertiseId($datas)
    {
        $uuid = $datas->uuid;
        $uuid = str_replace('-', '',$uuid);

        $major = $datas->major_id;
        $minor = $datas->minor_id;

        $major = dechex($major);

        $majorlen = strlen($major);
        if($majorlen == 3)
        {
            $major = '0'.$major;
        }
        else if($majorlen == 2)
        {
            $major = '00'.$major;
        }
        else if($majorlen == 1)
        {
            $major = '000'.$major;
        }

        //$major = '0'.dechex($major);
        $minor = dechex($minor);

        $minorlen = strlen($minor);
        if($minorlen == 3)
        {
            $minor = '0'.$minor;
        }
        else if($minorlen == 2)
        {
            $minor = '00'.$minor;
        }
        else if($minorlen == 1)
        {
            $minor = '000'.$minor;
        }


        $str = $uuid.$major.$minor;

        $binarydata = pack('H*', $str);

        return base64_encode($binarydata);
    }
}
