<?php

namespace  App\Modules\Beaconmanager\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Http\Request;

use App\Models\Projects;
use Config;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use PulkitJalan\Google\Facades\Google;


class Webservice extends Model
{
    protected $hadoop_db_conn;
    protected $connection;
    public $JsonArray   = array();

    public function __construct()
    {
        $Data = json_decode(stripslashes(htmlspecialchars_decode(urldecode(\Request::input('reqObject')))));
        $TaskData = $Data->taskData;
        $project_conn_secret = $TaskData->secret_token;
        if($project_conn_secret != '')
        {
            $data = Projects::where('secret',trim($project_conn_secret))->first();

            if(count($data))
            {


                $schemaName = $data->db;


                $secret = $data->db_user;
                $password = \Crypt::decrypt($data->db_password);

                Config::set('database.connections.'.$schemaName, array(
                    'driver'    => 'mysql',
                    'host'      => 'localhost',
                    'database'  => $schemaName,
                    'username'  => $secret,
                    'password'  => $password,
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix'    => '',
                ));

                $getDefaultdbConnections = \Config::get("database.connections.".$schemaName);

                //$project_conn = 'project_conn'.$data->id;
                //Session::set('project_conn_id',$data->id);
                //Session::set('infra_database_conn', $schemaName);
                Session::set('mysql_bm_database_conn',$schemaName);
                $this->connection = $schemaName;
                $this->hadoop_db_conn = $schemaName;
            }
            else
            {
                $this->JsonArray['data'] = '';
                $this->JsonArray['message'] = "Wrong Connection for Application please check your Secret Token.";
                $this->JsonArray['status_code'] = 204;
                header("content-type: application/json");
                echo json_encode($this->JsonArray);exit;
            }

        }
        else
        {
            $this->JsonArray['data'] = '';
            $this->JsonArray['message'] = "Wrong Connection for Application please check your Secret Token.";
            $this->JsonArray['status_code'] = 204;
            header("content-type: application/json");
            echo json_encode($this->JsonArray);exit;
        }
    }

    public function deleteBeacons($bName)
    {
        $googleClient = Google::getClient();
        $googleClient->useApplicationDefaultCredentials();

        $Proximitybeacon = Google::make('proximitybeacon');

        //$bName = "beacons/1!fda50693a4e24fb1afcfc6eb0764782510081600";

        $deleteBeacon = $Proximitybeacon->beacons->delete($bName);

        echo "<pre>";
        print_r($deleteBeacon);
        echo "</pre>";
        exit;
    }

    /**
     * Use To Post Beacon.
     *
     * Request Object :
     * {"task":"postBeacon","taskData":{"name":"Beacon1", mac_id":"6A:60:A1:55:E5:22","uuid":"2f234454-cf6d-4a0f-adf2-f4911ba9ffa6","type":"ibeacon","major_id":"5986","minor_id":"6892","beacon_X":"23.03725","beacon_Y":"72.5121637","status":"1","secret_token":"03EB4BB5","location":"adress xyz", "placeId":""}}
     *
     */
    public function postBeacon($TaskData)
    {

        /*$json = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?latlng=23.0371522,72.51219930000002&key=AIzaSyCa2HEyzmSepTQnzX9deSUhBj_G9x9cSpc'); // this WILL do an http request for you
        $data = json_decode($json);
        echo "<pre>";
        print_r($data->results[0]->place_id);
        echo "</pre>";
        exit;*/

        //echo $data->{'token'};

        /*$bName = "beacons/1!fda50693a4e24fb1afcfc6eb07647825ffe0ffe1";

        $deleteBeacon = $this->deleteBeacons($bName);

        echo "<pre>";
        print_r($deleteBeacon);
        echo "</pre>";
        exit;*/


        /*$getDefaultServiceConfigFile = \Config::get("google.service.file");

        echo $getDefaultServiceConfigFile;
        exit;*/

        if($TaskData->placeId == '')
        {
            $json = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?latlng='.$TaskData->beacon_X.','.$TaskData->beacon_Y.'&key=AIzaSyCa2HEyzmSepTQnzX9deSUhBj_G9x9cSpc'); // this WILL do an http request for you
            $data = json_decode($json);

            $TaskData->placeId = $data->results[0]->place_id;

        }


        $data = array(
            'name'       => $TaskData->name,
            'type'       => $TaskData->type,
            'macid'      => $TaskData->mac_id,
            'major_id'   => $TaskData->major_id,
            'minor_id'   => $TaskData->minor_id,
            'location'   => $TaskData->location,
            'placeId'    => $TaskData->placeId,
            'beacon_X'   => $TaskData->beacon_X,
            'beacon_Y'   => $TaskData->beacon_Y,
            'uuid'       => $TaskData->uuid,
            'status'     => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        );

        $lastId = Beacon::insertGetId($data);

        // Register beacon in google...
        $registerDatas = $this->registerBeacons($TaskData);



        $beaconName = $registerDatas->beaconName;

        $Beacon              = Beacon::find($lastId);
        $Beacon->beaconName  = $beaconName;
        $Beacon->advertiseId = $registerDatas['advertisedId']['id'];
        $Beacon->save();

        $final_array = Beacon::get()->toArray();

        $this->JsonArray['data']        = $final_array;
        $this->JsonArray['message']     = "Beacon Data Insert Successfully";
        $this->JsonArray['status_code'] = 200;
        header("content-type: application/json");
        echo json_encode($this->JsonArray);exit;
    }

    /**
     * Use To Register User.
     *
     * Request Object :
     * {"task":"getBeacon","taskData":{}}
     *
     */
    public function getBeacon($TaskData)
    {
        $final_array = Beacon::get()->toArray();

        if ($final_array > 0) {

        	$this->JsonArray['data']        = $final_array;
        	$this->JsonArray['message']     = "Beacon Data List View Successfully";
            $this->JsonArray['status_code'] = 200;
            header("content-type: application/json");
            echo json_encode($this->JsonArray);exit;
        }else{
            $this->JsonArray['data']        = '';
            $this->JsonArray['message']     = "Beacon Data List Empty";
            $this->JsonArray['status_code'] = 204;
            header("content-type: application/json");
            echo json_encode($this->JsonArray);exit;
        }
    }


    /**
     * Use To Register User.
     *
     * Request Object :
     * {"task":"getBeacon","taskData":{}}
     *
     */
    public function getIot($TaskData)
    {
        $final_array = Iot::get()->toArray();

        if ($final_array > 0) {

            $this->JsonArray['data']        = $final_array;
            $this->JsonArray['message']     = "Iot Data List View Successfully";
            $this->JsonArray['status_code'] = 200;
            header("content-type: application/json");
            echo json_encode($this->JsonArray);exit;
        }else{
            $this->JsonArray['data']        = '';
            $this->JsonArray['message']     = "Iot Data List Empty";
            $this->JsonArray['status_code'] = 204;
            header("content-type: application/json");
            echo json_encode($this->JsonArray);exit;
        }
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