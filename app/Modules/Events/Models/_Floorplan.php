<?php
namespace App\Modules\Events\Models;

use DB;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Floorplan extends Eloquent
{
    protected $table    = 'floorplan';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }

    public $FloorplanTable = 'floorplan';


    public function GetFloorPlan()
    {

        $Floorplans = DB::table($this->FloorplanTable)->get();
        return $Floorplans;
    }

    public function AddFloorplan($Post)
    {

        $InsertedID = DB::connection()->table($this->FloorplanTable)->insertGetId($Post);

        return $InsertedID;
    }

    public function GetFloorPlanData($FloorplanID)
    {

        $Floorplan = DB::table($this->FloorplanTable)->where('id', '=', $FloorplanID)->first();

        return $Floorplan;
    }

    public function UpdateFloorplan($Post, $Id){

        $UpdateData = DB::table('floorplan')->where('id',$Id)->update($Post);

        return true;
    }
    public function getBeacons()
    {
        $Beacons = DB::table('beacon')->where('floorplan_id',0)->get();
        return $Beacons;
    }

    public function getFloorBeacons($floorplan_id)
    {
        $Beacons = DB::table('beacon')->where('floorplan_id',$floorplan_id)->where('beacon_x','!=','')->where('beacon_x','!=','NaN')->get();
        return $Beacons;
    }

    public function getFloorCalibrator($floorplan_id)
    {
        $Calibrators = DB::table('calibrator')->where('floorplan_id',$floorplan_id)->get();
        return $Calibrators;
    }

    public function getFloorPoi($floorplan_id)
    {
        $Pois = DB::table('poi')->where('floorplan_id',$floorplan_id)->get();
        return $Pois;
    }

    public function getFloorGateway($floorplan_id)
    {
        $Gateway = DB::table('gateway')->where('floorplan_id',$floorplan_id)->get();
        return $Gateway;
    }

    public function GetFloorPlanNode($floorplan_id)
    {
        $FpNodes = DB::table('nodes')->where('floorplan_id',$floorplan_id)->get();
        return $FpNodes;
    }

    public function GetAssignedNode($id)
    {
        $AssignedNodes = DB::table('node_relation')->where('floorplan_id', $id)->get();
        $line_array = array();
        if (count($AssignedNodes) > 0) {
            foreach ($AssignedNodes as $key => $value) {
                $first_node = DB::table('nodes')->where('id', $value->node_id)->get(array('relation'));
                $second_node = DB::table('nodes')->where('id', $value->rel_node_id)->get(array('relation'));
                $line_array[] = array(
                    'node_id' => $value->node_id,
                    'rel_node_id' => $value->rel_node_id,
                    'node_unique' => $first_node[0]->relation,
                    'rel_node_unique' => $second_node[0]->relation,
                    'distance'=>$value->distance
                );
            }
        }
        return $line_array;
    }

    public function DeleteFloorplan($FloorplanIDs)
    {
        $FloorplanIDs = explode(',', $FloorplanIDs);
        foreach ($FloorplanIDs as $key => $FloorplanID) {
            $Image = DB::table($this->FloorplanTable)->where('id', '=', $FloorplanID)->pluck('image');
            $ImagePath = public_path() . $Image[0];
            File::delete($ImagePath);
            $UpdateFPID = DB::table('beacon')->where('id', '=', $FloorplanID)->update(array('id' => 0, 'beacon_X' => '', 'beacon_Y' => ''));
            $DeleteFP = DB::table($this->FloorplanTable)->where('id', '=', $FloorplanID)->delete();
            $Deletenodes = Nodes::where('id', '=', $FloorplanID)->delete();
            $Deletepois = Poi::where('id', '=', $FloorplanID)->delete();
            $Deletecalibrator = Calibrator::where('id', '=', $FloorplanID)->delete();
            $Deletegateway = Gateway::where('id', '=', $FloorplanID)->delete();

            // Store in audit
            $auditData                = array();
            $auditData['user_id']     = \Auth::user()->id;
            $auditData['section']     = 'floorplan';
            $auditData['action']      = 'floorplan.delete';
            $auditData['time_stamp']  = time();
            $auditData['device_id']   = \Request::ip();
            $auditData['device_type'] = 'web';
            auditTrackRecord($auditData);
        }
        return true;
    }

    //AJAX FUNCTIONS
    public function ajaxInsertNodesPaths($post)
    {
        $nodes = array();
        $nodes_relation = array();
        $beacons = array();
        $calibrator = array();
        $pois = array();
        $x_ratio = $post['flooplan_width']/$post['image_width'];
        $y_ratio =  $post['flooplan_height']/$post['image_height'];
        //insert or update nodes
        for($i=0;$i<count($post['nodes']);$i++)
        {
            $nodes = array(
                'name'=>$post['nodes'][$i]['tip'],
                'type'=>'Root',
                'floorplan_id'=>$post['floorPlan'],
                'node_X'=>$post['nodes'][$i]['x'],
                'node_Y'=>$post['nodes'][$i]['y'],
                'display_X'=>$post['nodes'][$i]['x']*$x_ratio,
                'display_Y'=>$post['nodes'][$i]['y']*$y_ratio,
                'relation'=>$post['nodes'][$i]['relation']
            );
            if(isset($post['nodes'][$i]['id']))
            {
                DB::table('nodes')->where('id',$post['nodes'][$i]['id'])->update($nodes);
            }
            else
            {
                DB::table('nodes')->insert($nodes);
            }
        }

        //truncate  nodes for particular floorplan and insert again
        $get_all_nodes = Nodes::where('floorplan_id',$post['floorPlan'])->get()->toArray();
        DB::table('node_relation')->where('floorplan_id',$post['floorPlan'])->delete();
        for($j=0;$j<count($post['paths']);$j++)
        {
            $node_id_index = array_search($post['paths'][$j]['node_unique'], array_column($get_all_nodes, 'relation'));
            $rel_node_id_index = array_search($post['paths'][$j]['rel_node_unique'], array_column($get_all_nodes, 'relation'));
            $a = $get_all_nodes[$node_id_index]['node_X']-$get_all_nodes[$rel_node_id_index]['node_X'];
            $b = $get_all_nodes[$node_id_index]['node_Y']-$get_all_nodes[$rel_node_id_index]['node_Y'];
            $nodes_relation[] = array(
                'node_id'=>$get_all_nodes[$node_id_index]['id'],
                'rel_node_id'=>$get_all_nodes[$rel_node_id_index]['id'],
                'floorplan_id'=>$post['floorPlan'],
                'distance'=>sqrt($a*$a+$b*$b)
            );
        }
        DB::table('node_relation')->insert($nodes_relation);

        //Update beacons positions
        for($b=0;$b<count($post['beacons']);$b++)
        {
            $beacons['beacon_X'] = $post['beacons'][$b]['x'];
            $beacons['beacon_Y'] = $post['beacons'][$b]['y'];
            $beacons['display_X'] = $post['beacons'][$b]['x']*$x_ratio;
            $beacons['display_Y'] = $post['beacons'][$b]['y']*$y_ratio;
            DB::table('beacon')->where('id',$post['beacons'][$b]['id'])->update($beacons);
        }

        //update unused beacons
        DB::table('beacon')->where('floorplan_id',$post['floorPlan'])->where('beacon_X','')->orWhere('beacon_X','NaN')->update(array('floorplan_id'=>0));

        //insert or update calibrators
        for($i=0;$i<count($post['calibrators']);$i++)
        {
            $calibrators = array(
                'name'=>$post['calibrators'][$i]['tip'],
                'floorplan_id'=>$post['floorPlan'],
                'node_X'=>$post['calibrators'][$i]['x'],
                'node_Y'=>$post['calibrators'][$i]['y'],
                'display_X'=>$post['calibrators'][$i]['x']*$x_ratio,
                'display_Y'=>$post['calibrators'][$i]['y']*$y_ratio
            );
            if(isset($post['calibrators'][$i]['id']))
            {
                DB::table('calibrator')->where('id',$post['calibrators'][$i]['id'])->update($calibrators);
            }
            else
            {
                DB::table('calibrator')->insert($calibrators);
            }
        }

        //insert or update pois
        for($i=0;$i<count($post['pois']);$i++)
        {
            $pois = array(
                'name'=>'',
                'floorplan_id'=>$post['floorPlan'],
                'connect'=>$post['pois'][$i]['connect'],
                'area'=>$post['pois'][$i]['area'],
                'node_id'=>$post['pois'][$i]['node'],
                'poi_X'=>$post['pois'][$i]['x'],
                'poi_Y'=>$post['pois'][$i]['y'],
                'display_X'=>$post['pois'][$i]['x']*$x_ratio,
                'display_Y'=>$post['pois'][$i]['y']*$y_ratio
            );
            if(isset($post['pois'][$i]['id']))
            {
                DB::table('poi')->where('id',$post['pois'][$i]['id'])->update($pois);
            }
            else
            {
                Poi::create($pois);
            }

        }

        //insert or update gateways
        for($i=0;$i<count($post['gateways']);$i++)
        {
            $gateway = array(
                'name'=>$post['gateways'][$i]['tip'],
                'floorplan_id'=>$post['floorPlan'],
                'gateway_X'=>$post['gateways'][$i]['x'],
                'gateway_Y'=>$post['gateways'][$i]['y'],
                'display_X'=>$post['gateways'][$i]['x']*$x_ratio,
                'display_Y'=>$post['gateways'][$i]['y']*$y_ratio
            );
            if(isset($post['gateways'][$i]['id']))
            {
                DB::table('gateway')->where('id',$post['gateways'][$i]['id'])->update($gateway);
            }
            else
            {
                Gateway::create($gateway);
            }

        }

        //Delete Beacons
        for($db=0;$db<count($post['beacon_delete']);$db++)
        {
            $beacons['beacon_X'] = '';
            $beacons['beacon_Y'] = '';
            $beacons['floorplan_id'] = 0;
            DB::table('beacon')->where('id',$post['beacon_delete'][$db]['id'])->update($beacons);
        }

        //Delete calibrators
        for($db=0;$db<count($post['calibrator_delete']);$db++)
        {
            Calibrator::where('id',$post['calibrator_delete'][$db]['id'])->delete();
        }

        //Delete pois
        for($db=0;$db<count($post['poi_delete']);$db++)
        {
            Poi::where('id',$post['poi_delete'][$db]['id'])->delete();
        }

        //Delete gateway
        for($db=0;$db<count($post['gateway_delete']);$db++)
        {
            Gateway::where('id',$post['gateway_delete'][$db]['id'])->delete();
        }

        //Delete Nodes
        $connected_nodes = array();
        for($dn=0;$dn<count($post['node_delete']);$dn++)
        {
            DB::table('nodes')->where('id',$post['node_delete'][$dn]['id'])->delete();
            //reserve connected nodes relation
            $connected_nodes[] = DB::table('node_relation')->where('node_id',$post['node_delete'][$dn]['id'])->lists('rel_node_id');
            $connected_nodes[] = DB::table('node_relation')->where('rel_node_id',$post['node_delete'][$dn]['id'])->lists('node_id');
            DB::table('node_relation')->where('node_id',$post['node_delete'][$dn]['id'])->orWhere('rel_node_id',$post['node_delete'][$dn]['id'])->delete();
        }
        //delete connected nodes which do not have connection with any other node
        $final_connected_nodes = array();
        for($cn=0;$cn<count($connected_nodes);$cn++)
        {
            for($cni=0;$cni<count($connected_nodes[$cn]);$cni++)
            {
                $final_connected_nodes[] = $connected_nodes[$cn][$cni];
            }
        }
        array_values(array_unique($final_connected_nodes,SORT_REGULAR));
        for($dd=0;$dd < count($final_connected_nodes);$dd++)
        {
            $count = DB::table('node_relation')->where('node_id',$final_connected_nodes[$dd])->orWhere('rel_node_id',$final_connected_nodes[$dd])->count();
            if($count == 0 )
            {
                DB::table('nodes')->where('id',$final_connected_nodes[$dd])->delete();
            }
        }
        $node_with_id = DB::table('nodes')->where('floorplan_id',$post['floorPlan'])->get();
        $beacon_with_id = $this->getFloorBeacons($post['floorPlan']);
        $node_relation = $this->GetAssignedNode($post['floorPlan']);
        $calibrators_with_id = $this->getFloorCalibrator($post['floorPlan']);
        $pois_with_id = DB::table('poi')->where('floorplan_id',$post['floorPlan'])->get();
        $gateway_with_id = DB::table('gateway')->where('floorplan_id',$post['floorPlan'])->get();
        $data['code'] = '200';
        $data['nodes'] = json_encode($node_with_id);
        $data['beacon'] = json_encode($beacon_with_id);
        $data['calibrator'] = json_encode($calibrators_with_id);
        $data['poi'] = json_encode($pois_with_id);
        $data['gateway'] = json_encode($gateway_with_id);
        $data['relation'] = json_encode($node_relation);
        return $data;
    }

    public function ajaxAddBeacon($request)
    {
        $Beacon = DB::table('beacon')->where('floorplan_id',0)->where('status',1)->first();
        $data['id']=$Beacon->id;
        $data['name']=$Beacon->name;
        $beacon_floorplan = DB::table('beacon')->where('id',$Beacon->id)->update(['floorplan_id'=>$request->floorPlan]);
        return json_encode($data);
    }

    public function ajaxExport($request)
    {
        $type = $request->type;
        $floorplan = $request->floorplan_id;
        $filename = $type.".xls";
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        if($type == 'beacon')
        {
            $beacons = Beacon::where('floorplan_id',$floorplan)->get()->toArray();
            $contents =  "S.No \t Name \t Major ID \t Minor ID \t UUID \t X \t Y \n";
            for ($i=0;$i<count($beacons);$i++)
            {
                $contents .= ($i+1)."\t".$beacons[$i]['name']."\t".$beacons[$i]['major_id']."\t".$beacons[$i]['minor_id']."\t".$beacons[$i]['uuid']."\t".$beacons[$i]['beacon_X']."\t".$beacons[$i]['beacon_Y']."\n";
            }
        }

        if($type == 'calibrator')
        {
            $calibrator = Calibrator::where('floorplan_id',$floorplan)->get()->toArray();
            $contents =  "S.No \t Name \t X \t Y \n";
            for ($i=0;$i<count($calibrator);$i++)
            {
                $contents .= ($i+1)."\t".$calibrator[$i]['name']."\t".$calibrator[$i]['node_X']."\t".$calibrator[$i]['node_Y']."\n";
            }
        }

        if($type == 'gateway')
        {
            $gateway = Gateway::where('floorplan_id',$floorplan)->get()->toArray();
            $contents =  "S.No \t Name \t Major ID \t Minor ID \t X \t Y \n";
            for ($i=0;$i<count($gateway);$i++)
            {
                $contents .= ($i+1)."\t".$gateway[$i]['name']."\t".$gateway[$i]['major_id']."\t".$gateway[$i]['minor_id']."\t".$gateway[$i]['gateway_X']."\t".$gateway[$i]['gateway_Y']."\n";
            }
        }

        if($type == 'poi')
        {
            $poi = Poi::where('floorplan_id',$floorplan)->get()->toArray();
            $contents =  "S.No \t Name \t X \t Y \n";
            for ($i=0;$i<count($poi);$i++)
            {
                $contents .= ($i+1)."\t".$poi[$i]['area']."\t".$poi[$i]['poi_X']."\t".$poi[$i]['poi_Y']."\n";
            }
        }


        $bytes_written = File::put(public_path("uploads/Exports/$filename"), $contents);
        if ($bytes_written === false)
        {
            die("Error writing to file");
        }
        else
        {
           return array("code"=>200,"path"=>url("uploads/Exports/$filename"));
        }
    }
}
