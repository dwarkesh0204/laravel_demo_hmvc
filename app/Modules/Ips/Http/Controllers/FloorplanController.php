<?php

namespace App\Modules\Ips\Http\Controllers;

use App\Models\Project_Devices;
use App\Modules\Ips\Models\Beacon;
use App\Modules\Ips\Models\Calibrator;
use App\Modules\Ips\Models\Gateway;
use App\Modules\Ips\Models\Nodes;
use App\Modules\Ips\Models\Floorplan;
use App\Modules\Ips\Models\Poi;
use App\Modules\Ips\Models\RssiLog;
use App\Modules\Ips\Models\NodeRelation;
use Session;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;
use App\Modules\Beaconmanager\Models\Iot;
use Carbon\Carbon;

class FloorplanController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->FloorplanModel = $this;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('ips::floorplan.index');
    }

    public function FloorplanData(Request $request)
    {
        $provinces = Floorplan::select('*');
        return Datatables::of($provinces)->make(true);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('ips::floorplan.create');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $Input = $request->all();

        //dd($Input);
        $this->validate($request, [
            'name'   => 'required',
            'image'  => 'required|mimes:jpeg,jpg,png',
            'width'  => 'required|between:0,99.99',
            'height' => 'required|between:0,99.99',
        ]);


        // Define The Storage Path Of The Note File
        $storage = public_path() . "/uploads/floorplan_images/";

        // Create A FileName From Uploaded File.
        $fileName = time() . $request->file('image')->getClientOriginalName();

        // Remove Spaces In The Filename And Convert To LowerCase.
        $fileName = str_replace(' ', '', strtolower($fileName));
        $file     = str_replace(' ', '', strtolower($request->file('image')->getClientOriginalName()));

        // Move The File To Storage Directory
        if ($request->file('image')->move($storage, $fileName)) {
            $FileNameStore = "/uploads/floorplan_images/" . $fileName;
        }

        $FloorplanAreas = '';
        $Input['FloorplanAreas'][] = $Input['areas'];

        if (is_array($Input['FloorplanAreas'])) {
            $FilterSection = array_filter($Input['FloorplanAreas']);
            if (count($FilterSection) > 0) {
                $FpSections     = array_reverse($FilterSection);
                $FloorplanAreas = implode(',', $FpSections);
            }
        }

        $Post = array();
        $Post['name']   = $Input['name'];
        $Post['image']  = $FileNameStore;
        $Post['width']  = $Input['width'];
        $Post['height'] = $Input['height'];
        $Post['areas']  = $FloorplanAreas;
        $Post['status'] = 1;

        $FloorplanID = Floorplan::insertGetId($Post);

        if ($FloorplanID) {

            // Store in audit
            $auditData                = array();
            $auditData['user_id']     = \Auth::user()->id;
            $auditData['section']     = 'floorplan';
            $auditData['action']      = 'floorplan.create';
            $auditData['time_stamp']  = time();
            $auditData['device_id']   = \Request::ip();
            $auditData['device_type'] = 'web';
            //auditTrackRecord($auditData);

            return redirect()->route('floorplan.index')->with('flash_message', 'Floorplan created successfully');
        } else {
            return redirect()->back()->with('flash_message', 'Floorplan not added!');
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id){

        $Input = $request->all();

        $this->validate($request, [
            'name'   => 'required',
            'image'  => 'mimes:jpeg,jpg,png',
            'width'  => 'required|between:0,99.99',
            'height' => 'required|between:0,99.99',
        ]);

        //$Image = DB::table('floorplan')->where('id', '=', $id)->lists('image');
        $Image = Floorplan::where('id',$id)->pluck('image');

        if ($request->hasFile('image')) {
            // Define The Storage Path Of The Note File
            $storage = public_path()."/uploads/floorplan_images/";

            // Create A FileName From Uploaded File.
            $fileName = $request->file('image')->getClientOriginalName();

            // Remove Spaces In The Filename And Convert To LowerCase.
            $fileName = str_replace(' ', '', strtolower($fileName));
            $file     = str_replace(' ', '', strtolower($request->file('image')->getClientOriginalName()));

            // Move The File To Storage Directory
            if($request->file('image')->move($storage,$fileName)){
                $FileNameStore = "/uploads/floorplan_images/".$fileName;
            }

            $ImagePath = public_path().$Image[0];
            File::delete($ImagePath);

        }elseif(!$request->hasFile('image')) {
            $FileNameStore = $Image[0];
        }

        $CurrentSection = Floorplan::where('id',$id)->pluck('areas');
        if (!empty($CurrentSection[0])) {
            $CurrentSection = explode(',', $CurrentSection[0]);
        }

        $FloorplanAreas = '';
        if (is_array($Input['areas'])) {
            $FilterSection  = array_filter($Input['areas']);
            if (count($FilterSection) > 0) {
                if (count($CurrentSection) > 0 && count($FilterSection) > count($CurrentSection)) {
                    $FilterSection = array_reverse($FilterSection);
                }
                $FloorplanAreas = implode(',', $FilterSection);
            }
        }

        $Post = array();
        $Post['name']   = $Input['name'];
        $Post['image']  = $FileNameStore;
        $Post['width']  = $Input['width'];
        $Post['height'] = $Input['height'];
        $Post['areas']  = $FloorplanAreas;

        $this->FloorplanModel->UpdateFloorplan($Post,$id);

        // Store in audit
        $auditData                = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'floorplan';
        $auditData['action']      = 'floorplan.edit';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
       // auditTrackRecord($auditData);

        Session::flash('edit_message', 'Floorplan successfully updated!');
        return redirect()->back();
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $FloorplanData = Floorplan::where('id',$id)->first();
        $AreasArray = array();

        if (!empty($FloorplanData->areas)) {
            $AreasArray = explode(',', $FloorplanData->areas);
        }

        $image               = getimagesize(public_path() . $FloorplanData->image);
        $ImageWidth          = $image[0];
        $ImageHeight         = $image[1];

        $AllBeacons          = Beacon::where('floorplan_id',0)->get();

        $FloorBeacons        = json_encode(Beacon::where('floorplan_id',$id)->where('beacon_x','!=','')->where('beacon_x','!=','NaN')->get());
        $FloorplanNodes      = json_encode(Nodes::where('floorplan_id',$id)->get());
        $AssignedNodes       = json_encode($this->FloorplanModel->GetAssignedNode($id));
        $FloorCalibrators    = json_encode(Calibrator::where('floorplan_id',$id)->get());
        $FloorPois           = json_encode(Poi::where('floorplan_id',$id)->get());
        $FloorGateway        = json_encode(Gateway::where('floorplan_id',$id)->get());
        $FloorNodes_dropdown = Nodes::where('floorplan_id',$id)->pluck('name','id')->toArray();
        return view('ips::floorplan.edit', compact('FloorplanData', 'AllBeacons', 'id', 'AreasArray', 'FloorplanNodes', 'ImageWidth', 'ImageHeight', 'AssignedNodes', 'FloorNodes_dropdown','FloorBeacons','FloorCalibrators','FloorPois','FloorGateway'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        $FloorplanIDs = $request->input('floorPlanIDs');
        $this->FloorplanModel->DeleteFloorplan($FloorplanIDs);

        return redirect()->back()->with('delete_message', 'Floorplan Deleted!');
    }

    //ajax functions
    /**
     * @param Request $request
     * @return mixed
     */
    public function ajaxInsertNodesPaths(Request $request)
    {
        return $this->FloorplanModel->ajaxInsertNodesPathsData($request);
    }

    /**
     * @param Request $request
     */
    public function ajaxAddBeacon(Request $request)
    {
        $add_beacon = $this->FloorplanModel->ajaxAddBeaconData($request);
        echo $add_beacon;
    }

    public function ajaxExport(Request $request)
    {
        return $this->FloorplanModel->ajaxExportData($request);
    }

    /*custom function copyed from model*/
    public function UpdateFloorplan($Post, $Id){

        $UpdateData = Floorplan::where('id',$Id)->update($Post);

        return true;
    }

    public function GetAssignedNode($id)
    {
        $AssignedNodes = NodeRelation::where('floorplan_id', $id)->get();
        $line_array = array();
        if (count($AssignedNodes) > 0) {
            foreach ($AssignedNodes as $key => $value) {
                $first_node   = Nodes::where('id', $value->node_id)->get(array('relation'));
                $second_node  = Nodes::where('id', $value->rel_node_id)->get(array('relation'));
                $line_array[] = array(
                    'node_id'         => $value->node_id,
                    'rel_node_id'     => $value->rel_node_id,
                    'node_unique'     => $first_node[0]->relation,
                    'rel_node_unique' => $second_node[0]->relation,
                    'distance'        => $value->distance
                );
            }
        }

        return $line_array;
    }

    public function DeleteFloorplan($FloorplanIDs)
    {
        $FloorplanIDs = explode(',', $FloorplanIDs);

        foreach ($FloorplanIDs as $key => $FloorplanID) {

            $Image            = Floorplan::where('id',$FloorplanID)->pluck('image');
            $ImagePath        = public_path() . $Image[0];
            \File::delete($ImagePath);
            $UpdateFPID       = Beacon::where('floorplan_id',$FloorplanID)->update(array('id' => 0, 'beacon_X' => '', 'beacon_Y' => ''));
            $Deletenodes      = Nodes::where('floorplan_id',$FloorplanID)->delete();
            $Deletenodesrel   = NodeRelation::where('floorplan_id',$FloorplanID)->delete();
            $Deletepois       = Poi::where('floorplan_id',$FloorplanID)->delete();
            $deviceids        = Gateway::where('floorplan_id', '=', $FloorplanID)->pluck('name')->toArray();
            $Deletecalibrator = Calibrator::where('floorplan_id',$FloorplanID)->delete();
            $Deletegateway    = Gateway::where('floorplan_id',$FloorplanID)->delete();
            $DeleteFP         = Floorplan::where('id',$FloorplanID)->delete();

            if(count($deviceids))
            {
                $pid = \Session::get('ips_conn_id');
                Project_Devices::whereIn('device_id',$deviceids)->where('project_id',$pid)->delete();
            }

            // Store in audit
            $auditData                = array();
            $auditData['user_id']     = \Auth::user()->id;
            $auditData['section']     = 'floorplan';
            $auditData['action']      = 'floorplan.delete';
            $auditData['time_stamp']  = time();
            $auditData['device_id']   = \Request::ip();
            $auditData['device_type'] = 'web';
            //auditTrackRecord($auditData);
        }

        return true;
    }

    //AJAX FUNCTIONS
    public function ajaxInsertNodesPathsData($post)
    {
        $pid            = \Session::get('ips_conn_id');
        $nodes          = array();
        $nodes_relation = array();
        $beacons        = array();
        $calibrator     = array();
        $pois           = array();
        $x_ratio        = $post['flooplan_width']/$post['image_width'];
        $y_ratio        = $post['flooplan_height']/$post['image_height'];

        //insert or update nodes
        for($i=0;$i<count($post['nodes']);$i++)
        {
            $nodes = array(
                'name'         => $post['nodes'][$i]['tip'],
                'type'         => 'Root',
                'floorplan_id' => $post['floorPlan'],
                'node_X'       => $post['nodes'][$i]['x'],
                'node_Y'       => $post['nodes'][$i]['y'],
                'display_X'    => $post['nodes'][$i]['x']*$x_ratio,
                'display_Y'    => $post['nodes'][$i]['y']*$y_ratio,
                'relation'     => $post['nodes'][$i]['relation']
            );
            if(isset($post['nodes'][$i]['id']))
            {
                Nodes::where('id',$post['nodes'][$i]['id'])->update($nodes);
            }
            else
            {
                Nodes::insert($nodes);
            }
        }

        //truncate  nodes for particular floorplan and insert again
        $get_all_nodes = Nodes::where('floorplan_id',$post['floorPlan'])->get()->toArray();
        NodeRelation::where('floorplan_id',$post['floorPlan'])->delete();

        for($j=0; $j<count($post['paths']); $j++)
        {
            $node_id_index     = array_search($post['paths'][$j]['node_unique'], array_column($get_all_nodes, 'relation'));
            $rel_node_id_index = array_search($post['paths'][$j]['rel_node_unique'], array_column($get_all_nodes, 'relation'));
            $a                 = $get_all_nodes[$node_id_index]['node_X']-$get_all_nodes[$rel_node_id_index]['node_X'];
            $b                 = $get_all_nodes[$node_id_index]['node_Y']-$get_all_nodes[$rel_node_id_index]['node_Y'];
            $nodes_relation[]  = array(
                'node_id'      => $get_all_nodes[$node_id_index]['id'],
                'rel_node_id'  => $get_all_nodes[$rel_node_id_index]['id'],
                'floorplan_id' => $post['floorPlan'],
                'distance'     => sqrt($a*$a+$b*$b)
            );
        }
        NodeRelation::insert($nodes_relation);

        //Update beacons positions
        for($b=0; $b<count($post['beacons']); $b++)
        {
            if($post['beacons'][$b]['temp'])
            {
                $beacon_query = Beacon::on(\Session::get('mysql_bm_database_conn'));
                $bm_beacon    = $beacon_query->where('macid', $post['beacons'][$b]['macid'])->first();

                if(count($bm_beacon))
                {
                    $ips_beacon = new Beacon();
                    $ips_beacon->name         = $bm_beacon->name;
                    $ips_beacon->type         = $bm_beacon->type;
                    $ips_beacon->floorplan_id = $post['floorPlan'];
                    $ips_beacon->major_id     = $bm_beacon->major_id;
                    $ips_beacon->minor_id     = $bm_beacon->minor_id;
                    $ips_beacon->uuid         = $bm_beacon->uuid;
                    $ips_beacon->macid        = $bm_beacon->macid;
                    $ips_beacon->beacon_X     = $post['beacons'][$b]['x'];
                    $ips_beacon->beacon_Y     = $post['beacons'][$b]['y'];
                    $ips_beacon->display_X    = $post['beacons'][$b]['x']*$x_ratio;
                    $ips_beacon->display_Y    = $post['beacons'][$b]['y']*$y_ratio;
                    $ips_beacon->status       = $bm_beacon->status;
                    $ips_beacon->created_at   = Carbon::now();
                    $ips_beacon->updated_at   = Carbon::now();
                    $ips_beacon->save();

                    $beacon_floorplan = $beacon_query->where('macid', $bm_beacon->macid)->update(['floorplan_id'=>$post['floorPlan'], 'project_id'=>$pid]);
                }
            }
            else
            {
                $beacons['beacon_X']  = $post['beacons'][$b]['x'];
                $beacons['beacon_Y']  = $post['beacons'][$b]['y'];
                $beacons['display_X'] = $post['beacons'][$b]['x']*$x_ratio;
                $beacons['display_Y'] = $post['beacons'][$b]['y']*$y_ratio;

                Beacon::where('id',$post['beacons'][$b]['id'])->update($beacons);
            }
        }

        //update unused beacons
        Beacon::where('floorplan_id',$post['floorPlan'])->where('beacon_X','')->orWhere('beacon_X','NaN')->update(array('floorplan_id'=>0));

        //insert or update calibrators
        for($i=0;$i<count($post['calibrators']);$i++)
        {
            $calibrators = array(
                'name'         => $post['calibrators'][$i]['tip'],
                'floorplan_id' => $post['floorPlan'],
                'node_X'       => $post['calibrators'][$i]['x'],
                'node_Y'       => $post['calibrators'][$i]['y'],
                'display_X'    => $post['calibrators'][$i]['x']*$x_ratio,
                'display_Y'    => $post['calibrators'][$i]['y']*$y_ratio
            );

            if(isset($post['calibrators'][$i]['id']))
            {
                Calibrator::where('id',$post['calibrators'][$i]['id'])->update($calibrators);
            }
            else
            {
                Calibrator::insert($calibrators);
            }
        }

        //insert or update pois
        for($i=0;$i<count($post['pois']);$i++)
        {
            $pois = array(
                'name'         => '',
                'floorplan_id' => $post['floorPlan'],
                'connect'      => $post['pois'][$i]['connect'],
                'area'         => $post['pois'][$i]['area'],
                'node_id'      => $post['pois'][$i]['node'],
                'poi_X'        => $post['pois'][$i]['x'],
                'poi_Y'        => $post['pois'][$i]['y'],
                'display_X'    => $post['pois'][$i]['x']*$x_ratio,
                'display_Y'    => $post['pois'][$i]['y']*$y_ratio
            );

            if(isset($post['pois'][$i]['id']))
            {
                Poi::where('id',$post['pois'][$i]['id'])->update($pois);
            }
            else
            {
                $Poi = new Poi();
                $Poi->name         = '';
                $Poi->floorplan_id = $post['floorPlan'];
                $Poi->connect      = $post['pois'][$i]['connect'];
                $Poi->area         = $post['pois'][$i]['area'];
                $Poi->node_id      = $post['pois'][$i]['node'];
                $Poi->poi_X        = $post['pois'][$i]['x'];
                $Poi->poi_Y        = $post['pois'][$i]['y'];
                $Poi->display_X    = $post['pois'][$i]['x']*$x_ratio;
                $Poi->display_Y    = $post['pois'][$i]['y']*$y_ratio;
                $Poi->save();
            }

        }

        //insert or update gateways
        for($i=0; $i<count($post['gateways']); $i++)
        {
            $gateway = array(
                'name'         => $post['gateways'][$i]['tip'],
                'floorplan_id' => $post['floorPlan'],
                'gateway_X'    => $post['gateways'][$i]['x'],
                'gateway_Y'    => $post['gateways'][$i]['y'],
                'display_X'    => $post['gateways'][$i]['x']*$x_ratio,
                'display_Y'    => $post['gateways'][$i]['y']*$y_ratio
            );

            if(isset($post['gateways'][$i]['id']))
            {
                Gateway::where('id',$post['gateways'][$i]['id'])->update($gateway);
            }
            else
            {
                $Gateway = new Gateway();
                $Gateway->name         = $post['gateways'][$i]['tip'];
                $Gateway->floorplan_id = $post['floorPlan'];
                $Gateway->gateway_X    = $post['gateways'][$i]['x'];
                $Gateway->gateway_Y    = $post['gateways'][$i]['y'];
                $Gateway->display_X    = $post['gateways'][$i]['x']*$x_ratio;
                $Gateway->display_Y    = $post['gateways'][$i]['y']*$y_ratio;

                if($Gateway->save())
                {
                    $Project_Devices = new Project_Devices();
                    $Project_Devices->project_id  = $pid;
                    $Project_Devices->device_id   = $post['gateways'][$i]['tip'];
                    $Project_Devices->mac_address = '';
                    $Project_Devices->save();
                }
            }
        }

        //Delete Beacons
        for($db=0; $db<count($post['beacon_delete']); $db++)
        {
            $beacon       = Beacon::find($post['beacon_delete'][$db]['id']);
            $beacon_query = Beacon::on(\Session::get('mysql_bm_database_conn'));
            $beacon_query->where('macid', $beacon->macid)->update(['floorplan_id'=>0, 'project_id'=>0]);

            Beacon::where('id',$post['beacon_delete'][$db]['id'])->delete();
        }

        //Delete calibrators
        for($db=0; $db<count($post['calibrator_delete']); $db++)
        {
            Calibrator::where('id',$post['calibrator_delete'][$db]['id'])->delete();
        }

        //Delete pois
        for($db=0; $db<count($post['poi_delete']); $db++)
        {
            Poi::where('id',$post['poi_delete'][$db]['id'])->delete();
        }

        //Delete gateway
        for($db=0; $db<count($post['gateway_delete']); $db++)
        {
            Gateway::where('id',$post['gateway_delete'][$db]['id'])->delete();
            Project_Devices::where('device_id',$post['gateway_delete'][$db]['tip'])->where('project_id',$pid)->delete();
        }

        //Delete Nodes
        $connected_nodes = array();
        for($dn=0; $dn<count($post['node_delete']); $dn++)
        {
            if(isset($post['node_delete'][$dn]['id']))
            {
                Nodes::where('id',$post['node_delete'][$dn]['id'])->delete();
                //reserve connected nodes relation
                $connected_nodes[] = NodeRelation::where('node_id',$post['node_delete'][$dn]['id'])->pluck('rel_node_id');
                $connected_nodes[] = NodeRelation::where('rel_node_id',$post['node_delete'][$dn]['id'])->pluck('node_id');
                NodeRelation::where('node_id',$post['node_delete'][$dn]['id'])->orWhere('rel_node_id',$post['node_delete'][$dn]['id'])->delete();
            }
        }

        //delete connected nodes which do not have connection with any other node
        $final_connected_nodes = array();
        for($cn=0; $cn<count($connected_nodes); $cn++)
        {
            for($cni=0; $cni<count($connected_nodes[$cn]); $cni++)
            {
                $final_connected_nodes[] = $connected_nodes[$cn][$cni];
            }
        }

        array_values(array_unique($final_connected_nodes,SORT_REGULAR));

        for($dd=0; $dd < count($final_connected_nodes); $dd++)
        {
            $count = NodeRelation::where('node_id',$final_connected_nodes[$dd])->orWhere('rel_node_id',$final_connected_nodes[$dd])->count();
            if($count == 0 )
            {
                Nodes::where('id',$final_connected_nodes[$dd])->delete();
            }
        }

        $node_with_id        = Nodes::where('floorplan_id',$post['floorPlan'])->get();
        $beacon_with_id      = Beacon::where('floorplan_id',$post['floorPlan'])->get();
        $node_relation       = $this->GetAssignedNode($post['floorPlan']);
        $calibrators_with_id = Calibrator::where('floorplan_id',$post['floorPlan'])->get();
        $pois_with_id        = Poi::where('floorplan_id',$post['floorPlan'])->get();
        $gateway_with_id     = Gateway::where('floorplan_id',$post['floorPlan'])->get();
        $data['code']        = '200';
        $data['nodes']       = json_encode($node_with_id);
        $data['beacon']      = json_encode($beacon_with_id);
        $data['calibrator']  = json_encode($calibrators_with_id);
        $data['poi']         = json_encode($pois_with_id);
        $data['gateway']     = json_encode($gateway_with_id);
        $data['relation']    = json_encode($node_relation);

        return $data;
    }

    public function ajaxAddBeaconData($request)
    {
        $data = array();
        $beacon_query     = Beacon::on(\Session::get('mysql_bm_database_conn'));
        $pid              = \Session::get('ips_conn_id');
        $selected_beacons = ($request->added_beacons != null)?$request->added_beacons:array();
        $beacon_query->where('floorplan_id',0)->where('project_id',0)->where('status',1);

        if(count($selected_beacons))
        {
           $beacon_query->WhereNotIn('macid',$selected_beacons);
        }

        $Beacon = $beacon_query->first();

        if(count($Beacon)>0)
        {
            $data['id']      = $Beacon->id;
            $data['name']    = $Beacon->name;
            $data['macid']   = $Beacon->macid;
            $data['success'] = 1;

            //$beacon_floorplan = Beacon::where('id',$Beacon->id)->update(['floorplan_id'=>$request->floorPlan]);
        }
        else
        {
            $data['message']= 'No Beacons Found, Please add beacons from Beacon Manager';
            $data['success']= 0;
        }

        return json_encode($data);
    }

    public function ajaxExportData($request)
    {
        $type      = $request->type;
        $floorplan = $request->floorplan_id;
        $filename  = $type.".xls";
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");

        if($type == 'beacon')
        {
            $beacons  = Beacon::where('floorplan_id',$floorplan)->get()->toArray();
            $contents =  "S.No \t Name \t Major ID \t Minor ID \t UUID \t X \t Y \n";

            for ($i=0;$i<count($beacons);$i++)
            {
                $contents .= ($i+1)."\t".$beacons[$i]['name']."\t".$beacons[$i]['major_id']."\t".$beacons[$i]['minor_id']."\t".$beacons[$i]['uuid']."\t".$beacons[$i]['beacon_X']."\t".$beacons[$i]['beacon_Y']."\n";
            }
        }

        if($type == 'calibrator')
        {
            $calibrator = Calibrator::where('floorplan_id',$floorplan)->get()->toArray();
            $contents   =  "S.No \t Name \t X \t Y \n";

            for ($i=0; $i<count($calibrator); $i++)
            {
                $contents .= ($i+1)."\t".$calibrator[$i]['name']."\t".$calibrator[$i]['node_X']."\t".$calibrator[$i]['node_Y']."\n";
            }
        }

        if($type == 'gateway')
        {
            $gateway  = Gateway::where('floorplan_id',$floorplan)->get()->toArray();
            $contents =  "S.No \t Name \t Major ID \t Minor ID \t X \t Y \n";

            for ($i=0;$i<count($gateway);$i++)
            {
                $contents .= ($i+1)."\t".$gateway[$i]['name']."\t".$gateway[$i]['major_id']."\t".$gateway[$i]['minor_id']."\t".$gateway[$i]['gateway_X']."\t".$gateway[$i]['gateway_Y']."\n";
            }
        }

        if($type == 'poi')
        {
            $poi      = Poi::where('floorplan_id',$floorplan)->get()->toArray();
            $contents =  "S.No \t Name \t X \t Y \n";

            for ($i=0; $i<count($poi); $i++)
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
    public function ajaxGetAvailableGateWay(Request $request)
    {
        $data = array();
        $selected_gateways  = ($request->Gate_Ways != null)?$request->Gate_Ways:array();
        $pid                = \Session::get('ips_conn_id');
        $assigned_pdIds     = Project_Devices::pluck('device_id')->toArray();
        $assigned_pdIds_all = array_unique(array_merge($assigned_pdIds,$selected_gateways));

        $query = Iot::on(\Session::get('mysql_bm_database_conn'));

        if(count($assigned_pdIds_all))
        {
           $query->WhereNotIn('device_id',$assigned_pdIds_all);
        }

        $availableGateway = $query->select('device_id')->first();

        if(count($availableGateway)>0)
        {
            $data['device_id'] = $availableGateway->device_id;
            $data['success']   = 1;
        }
        else
        {
            $data['message'] = 'No Devices Found, Please add devices from Beacon Manager';
            $data['success'] = 0;
        }

        return json_encode($data);
    }
}
