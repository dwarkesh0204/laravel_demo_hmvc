<?php

namespace App\Modules\Beaconmanager\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Beaconmanager\Models\Iot;
use App\Modules\Beaconmanager\Models\Beacon;
use Carbon\Carbon;
use Yajra\Datatables\Facades\Datatables;
use PulkitJalan\Google\Facades\Google;


class IotController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = Iot::orderBy('id','ASC')->paginate();

        return view('beaconmanager::iot.index',compact('items'));
    }

    public function iotData(Request $request)
    {
        $provinces = Iot::select('*');

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

        return view('beaconmanager::iot.create',compact('user_id'));
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
            'device_id' => 'required'
        ]);

        $Iot                   = new Iot();
        $Iot->title            = $request->title;
        $Iot->type             = $request->type;
        $Iot->device_id        = $request->device_id;
        $Iot->gateway_ssid     = $request->gateway_ssid;
        $Iot->gateway_password = $request->gateway_password;
        $Iot->wifi_name        = $request->wifi_name;
        $Iot->wifi_password    = $request->wifi_password;
        $Iot->mac_id           = $request->mac_id;
        $Iot->major_id         = $request->major_id;
        $Iot->minor_id         = $request->minor_id;
        $Iot->location         = $request->location;
        $Iot->placeId          = $request->placeId;
        $Iot->iot_X            = $request->iot_X;
        $Iot->iot_Y            = $request->iot_Y;
        $Iot->uuid             = $request->uuid;
        $Iot->created_at       = Carbon::now();
        $Iot->updated_at       = Carbon::now();
        $Iot->save();

        $lastIotId = $Iot->getAttribute('id');

        $Beacon              = new Beacon();
        $Beacon->name        = $request->title;
        $Beacon->type        = 'ibeacon';
        $Beacon->macid       = $request->mac_id;
        $Beacon->major_id    = $request->major_id;
        $Beacon->minor_id    = $request->minor_id;
        $Beacon->location    = $request->location;
        $Beacon->placeId     = $request->placeId;
        $Beacon->beacon_X    = $request->iot_X;
        $Beacon->beacon_Y    = $request->iot_Y;
        $Beacon->uuid        = $request->uuid;
        $Beacon->device_type = $request->device_type;
        $Beacon->created_at  = Carbon::now();
        $Beacon->updated_at  = Carbon::now();
        $Beacon->save();

        $lastId = $Beacon->getAttribute('id');

        $registerDatas = $this->registerBeacons($request);

        $beaconName = $registerDatas->beaconName;
        $Beacon              = Beacon::find($lastId);
        $Beacon->beaconName  = $beaconName;
        $Beacon->advertiseId = $registerDatas['advertisedId']['id'];
        $Beacon->save();


        $Iot              = Iot::find($lastIotId);
        $Iot->beaconName  = $beaconName;
        $Iot->advertiseId = $registerDatas['advertisedId']['id'];
        $Iot->save();

        return redirect()->route('iot.index')->with('flash_message','Iot created successfully');
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
        $item = Iot::find($id);
        $user_id = \Auth::user()->id;

        return view('beaconmanager::iot.edit',compact('item','user_id'));
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
            'device_id' => 'required',
        ]);
        $input = $request->all();
        Iot::find($id)->update($input);

        return redirect()->route('iot.index')->with('flash_message','Iot updated successfully');
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
            Iot::find($catId)->delete();
        }

        return redirect()->route('iot.index')->with('flash_message','Iot deleted successfully');
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
                //"type" => strtoupper($datas->type),
                "type" => 'IBEACON',
                "id" => $advertiseId
            ),
            "status" => "ACTIVE",
            "placeId" => $datas->placeId,
            "latLng" => array(
                //"latitude" => $datas->beacon_X,
                //"longitude" => $datas->beacon_Y
                "latitude" => $datas->iot_X,
                "longitude" => $datas->iot_Y
            ),
            "expectedStability" => "STABLE",
            "description" => $datas->title
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
