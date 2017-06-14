<?php

namespace App\Modules\Events\Http\Controllers;

use App\Modules\Events\Models\Nodes;
use Session;
use App\Modules\Events\Models\Floorplan;
use App\Modules\Events\Models\RssiLog;
use Illuminate\Http\Request;

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
        $this->FloorplanModel = new Floorplan();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $Floorplans = $this->FloorplanModel->GetFloorPlan();
        return view('events::floorplan.index', compact('Floorplans'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('events::floorplan.create');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $Input = $request->all();

        $this->validate($request, [
            'name' => 'required',
            'image' => 'required|mimes:jpeg,jpg,png',
            'width' => 'required|between:0,99.99',
            'height' => 'required|between:0,99.99',
        ]);

        // Define The Storage Path Of The Note File
        $storage = public_path() . "/uploads/floorplan_images/";

        // Create A FileName From Uploaded File.
        $fileName = time() . $request->file('image')->getClientOriginalName();

        // Remove Spaces In The Filename And Convert To LowerCase.
        $fileName = str_replace(' ', '', strtolower($fileName));
        $file = str_replace(' ', '', strtolower($request->file('image')->getClientOriginalName()));

        // Move The File To Storage Directory
        if ($request->file('image')->move($storage, $fileName)) {
            $FileNameStore = "/uploads/floorplan_images/" . $fileName;
        }

        $FloorplanAreas = '';
        if (is_array($Input['areas'])) {
            $FilterSection = array_filter($Input['areas']);
            if (count($FilterSection) > 0) {
                $FpSections = array_reverse($FilterSection);
                $FloorplanAreas = implode(',', $FpSections);
            }
        }

        $Post = array();
        $Post['name'] = $Input['name'];
        $Post['image'] = $FileNameStore;
        $Post['width'] = $Input['width'];
        $Post['height'] = $Input['height'];
        $Post['areas'] = $FloorplanAreas;

        $FloorplanID = $this->FloorplanModel->AddFloorplan($Post);

        if ($FloorplanID) {

            // Store in audit
            $auditData                = array();
            $auditData['user_id']     = \Auth::user()->id;
            $auditData['section']     = 'floorplan';
            $auditData['action']      = 'floorplan.create';
            $auditData['time_stamp']  = time();
            $auditData['device_id']   = \Request::ip();
            $auditData['device_type'] = 'web';
            auditTrackRecord($auditData);

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
        $Image = Floorplan::where('id',$id)->lists('image');

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
        auditTrackRecord($auditData);

        Session::flash('edit_message', 'Floorplan successfully updated!');
        return redirect()->back();
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $FloorplanData = $this->FloorplanModel->GetFloorPlanData($id);
        $AreasArray = array();
        if (!empty($FloorplanData->areas)) {
            $AreasArray = explode(',', $FloorplanData->areas);
        }
        $image = getimagesize(public_path() . $FloorplanData->image);
        $ImageWidth = $image[0];
        $ImageHeight = $image[1];

        $AllBeacons = $this->FloorplanModel->getBeacons();

        $FloorBeacons        = json_encode($this->FloorplanModel->getFloorBeacons($id));
        $FloorplanNodes      = json_encode($this->FloorplanModel->GetFloorPlanNode($id));
        $AssignedNodes       = json_encode($this->FloorplanModel->GetAssignedNode($id));
        $FloorCalibrators    = json_encode($this->FloorplanModel->getFloorCalibrator($id));
        $FloorPois           = json_encode($this->FloorplanModel->getFloorPoi($id));
        $FloorGateway        = json_encode($this->FloorplanModel->getFloorGateway($id));
        $FloorNodes_dropdown = Nodes::where('floorplan_id',$id)->lists('name','id')->toArray();

        return view('floorplan.edit', compact('FloorplanData', 'AllBeacons', 'id', 'AreasArray', 'FloorplanNodes', 'ImageWidth', 'ImageHeight', 'AssignedNodes', 'FloorNodes_dropdown','FloorBeacons','FloorCalibrators','FloorPois','FloorGateway'));
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
        return $this->FloorplanModel->ajaxInsertNodesPaths($request);
    }

    /**
     * @param Request $request
     */
    public function ajaxAddBeacon(Request $request)
    {
        $add_beacon = $this->FloorplanModel->ajaxAddBeacon($request);
        echo $add_beacon;
    }

    public function ajaxExport(Request $request)
    {
        return $this->FloorplanModel->ajaxExport($request);
    }
}
