<?php

namespace  App\Modules\Visitors\Models;

use App\Models\Project_Devices;
use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Http\Request;
use App\Models\Projects;
use Config;
use Illuminate\Support\Facades\Session;

class Visitorhadoop extends Model
{
    protected $hadoop_db_conn;
    protected $connection;
    public $JsonArray   = array();

    public function __construct()
    {
        $postdata = file_get_contents('php://input');
        $split = explode(':', $postdata,2); //split from : with 2 limited partitions
        $Project_deviceId = $split[0];
        $project = Project_Devices::where('device_id',$Project_deviceId)->select('project_id')->first();
        $project_conn_secret = $project->project_id;
        if($project_conn_secret != '')
        {
            $data = Projects::where('id',trim($project_conn_secret))->first();
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
                $project_conn = 'project_conn'.$data->id;
                Session::forget($project_conn);
                Session::set('project_conn_id',$data->id);
                Session::set($project_conn,$schemaName);
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
    public function SetLog($deviceId,$data_arr)
    {
        $count = count($data_arr);
        $insert_arr = array();
        if($count > 0)
        {
            $timestamp = date('Y-m-d H:i:s');
            for($i=0;$i<$count;$i++)
            {
                if($data_arr[$i] != '')
                {
                    $subdata_arr = explode(':', $data_arr[$i],2);//to get ble or wifi type
                    $beacon_type = $subdata_arr[0]; //beacon type
                    $beacon_data = $subdata_arr[1]; //beacon data

                    switch($beacon_type)
                    {
                        case 'ble':
                            $beacon_data_arr = explode(',',$beacon_data);
                            foreach ($beacon_data_arr as $index => $line)
                            {
                                if($line != '')
                                {
                                    $line_arr = explode(':',$line);
                                    if(count($line_arr) > 0)
                                    {
                                        $factory_id = ($line_arr[0] != '')?$line_arr[0]:'';
                                        $beacon_id = ($line_arr[1] != '')?$line_arr[1]:'';
                                        $hexaData = $this->getParts($line_arr[2],array(4,4,2));//major minor measured_power
                                        $major = ($hexaData[0] != '')?hexdec($hexaData[0]):'';
                                        $minor = ($hexaData[1] != '')?hexdec($hexaData[1]):'';
                                        $measured_power = ($hexaData[2] != '')?hexdec($hexaData[2]):'';
                                        $beacon_mac = ($line_arr[3] != '')?$line_arr[3]:'';
                                        $rssi = ($line_arr[4] != '')?hexdec($line_arr[4]):'';
                                        //echo $beacon_type .'<----->'. $beacon_mac .'<----->'. $device_id .'<----->'. $rssi .'<----->'. $factory_id .'<----->'. $major .'<----->'. $minor .'<----->'. $measured_power ;exit;
                                        if($beacon_type != '' && $beacon_mac != '' && $deviceId != '' && $rssi != ''  && $factory_id != ''  && $major != ''  && $minor != ''  )
                                        {
                                            $visitor_data = $this->getVisitorData($beacon_mac);
                                            $visitor_id = $visitor_data->id;
                                            $visitor_name = $visitor_data->visitor_name;
                                            $card_no = $visitor_data->card_no;
                                            if(count($visitor_data))
                                            {
                                                $insert_arr[] = "('".$beacon_mac."','".$deviceId."','".$minor."','".$major."','".$timestamp."','".$rssi."','".$factory_id."','".$beacon_id."','".$measured_power."','".$beacon_type."','".$visitor_id."','".$visitor_name."','".$card_no."')";
                                            }
                                        }
                                    }
                                }
                            }
                            break;
                        case 'wifi':
                            $beacon_data_arr = explode(',',$beacon_data);
                            foreach ($beacon_data_arr as $index => $line)
                            {
                                if($line != '')
                                {
                                    $line_arr = explode(':',$line);
                                    if(count($line_arr) > 0)
                                    {
                                        $factory_id = '';
                                        $beacon_id = '';
                                        $major = '';
                                        $minor = '';
                                        $measured_power = '';
                                        $beacon_mac = ($line_arr[1] != '')?$line_arr[1]:'';
                                        $rssi = ($line_arr[2] != '')?$line_arr[2]:'';

                                        if($beacon_mac != '' && $rssi != '')
                                        {
                                            $visitor_data = $this->getVisitorData($beacon_mac);
                                            $visitor_id = $visitor_data->id;
                                            $visitor_name = $visitor_data->visitor_name;
                                            $card_no = $visitor_data->card_no;

                                            if(count($visitor_data))
                                            {
                                                $insert_arr[] = "('".$beacon_mac."','".$deviceId."','".$minor."','".$major."','".$timestamp."','".$rssi."','".$factory_id."','".$beacon_id."','".$measured_power."','".$beacon_type."','".$visitor_id."','".$visitor_name."','".$card_no."')";
                                            }

                                        }
                                    }
                                }
                            }
                            break;
                    }
                }
            }
            if(count($insert_arr)>0)
            {
                $all_data = implode(',',$insert_arr);
                $insert_data = DB::connection('odbc')->statement("INSERT INTO TABLE ".$this->hadoop_db_conn.".visitors_logs VALUES ".$all_data );
            }

        }
    }
    public function getParts($string, $positions)
    {
        $parts = array();
        foreach ($positions as $position){
            $parts[] = substr($string, 0, $position);
            $string = substr($string, $position);
        }
        return $parts;
    }
    public function getVisitorData($mac)
    {
        $query = Visitor::select('id','name as visitor_name','card_no');
        $query->where('mac_add',$mac);
        $visitor = $query->first();
        return $visitor;
    }
}