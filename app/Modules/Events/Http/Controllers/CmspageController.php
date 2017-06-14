<?php

namespace App\Modules\Events\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Modules\Events\Http\Requests;

use App\Modules\Events\Models\Cmspage;

use App\Modules\Events\Models\Categories;

use Carbon\Carbon;

use Yajra\Datatables\Facades\Datatables;

class CmspageController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		$items = Cmspage::orderBy('id','DESC')->paginate();

		return view('events::cmspages.index',compact('items'));
	}

	public function CmspagesData(Request $request)
    {
        $provinces = Cmspage::select('*');

        return Datatables::of($provinces)->make(true);
    }

	function fetchCategoryTreeForOptions($parent = 0, $spacing = '', $user_tree_array = '')
    {
        if (!is_array($user_tree_array))
        {
            $user_tree_array = array();
        }
        else
        {
            $spacing .= '-';
        }

        $items = Categories::Select('id','title')->Where('status',1)->Where('parent',$parent)->orderBy('id','ASC')->paginate();

        if (count($items) > 0)
        {
            foreach ($items as $key => $value)
            {
                $user_tree_array[$value->id] = $spacing . '&nbsp;' . $value->title;
                $user_tree_array = $this->fetchCategoryTreeForOptions($value->id, $spacing . '&nbsp;', $user_tree_array);
            }
        }
        return $user_tree_array;
    }

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		$Categories    = $this->fetchCategoryTreeForOptions();

        return view('events::cmspages.create',compact('Categories'));
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
			'display_name' => 'required',
			'description'  => 'required',
			'cat_id'       => 'required'
		]);

		$cmspage = new Cmspage();
		$cmspage->cat_id       = $request->cat_id;
		$cmspage->display_name = $request->display_name;
		$cmspage->name         = $request->name;
		$cmspage->description  = $request->description;
		$cmspage->active       = $request->active;
		$cmspage->created_at   = Carbon::now();
		$cmspage->updated_at   = Carbon::now();
        $cmspage->save();

		// Store in audit
        $auditData = array();
		$auditData['user_id']     = \Auth::user()->id;
		$auditData['section']     = 'cmsPages';
		$auditData['action']      = 'cmsPages.create';
		$auditData['time_stamp']  = time();
		$auditData['device_id']   = \Request::ip();
		$auditData['device_type'] = 'web';
        //auditTrackRecord($auditData);

		return redirect()->route('cmspages.index')->with('flash_message','Page created successfully');
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		$item = Cmspage::find($id);
		$Categories    = $this->fetchCategoryTreeForOptions();

        return view('events::cmspages.edit',compact('item','Categories'));
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
			'display_name' => 'required',
			'description'  => 'required',
			'cat_id'       => 'required'
        ]);

        Cmspage::find($id)->update($request->all());

        // Store in audit
        $auditData = array();
		$auditData['user_id']     = \Auth::user()->id;
		$auditData['section']     = 'cmsPages';
		$auditData['action']      = 'cmsPages.edit';
		$auditData['time_stamp']  = time();
		$auditData['device_id']   = \Request::ip();
		$auditData['device_type'] = 'web';

        //auditTrackRecord($auditData);

        return redirect()->route('cmspages.index')->with('flash_message','Item updated successfully');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Request $request)
	{
		$Input       = $request->all();
		$pagesIDs    = $Input['pageIDs'];
		$pageIdArray = explode(',', $pagesIDs);

		foreach ($pageIdArray as $key => $pageId){
			Cmspage::find($pageId)->delete();

			// Store in audit
	        $auditData = array();
			$auditData['user_id']     = \Auth::user()->id;
			$auditData['section']     = 'cmsPages';
			$auditData['action']      = 'cmsPages.delete';
			$auditData['time_stamp']  = time();
			$auditData['device_id']   = \Request::ip();
			$auditData['device_type'] = 'web';
	        //auditTrackRecord($auditData);
		}

        return redirect()->route('cmspages.index')->with('flash_message','Item deleted successfully');
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
		$Cmspage    = Cmspage::find($id);

		if ($statusMode == 'unpublish'){
			$Cmspage->active = 0;
		}else{
			$Cmspage->active = 1;
		}
		$Cmspage->save();

		// Store in audit
        $auditData = array();
		$auditData['user_id']     = \Auth::user()->id;
		$auditData['section']     = 'cmsPages';
		$auditData['action']      = 'cmsPages.changeStatus';
		$auditData['time_stamp']  = time();
		$auditData['device_id']   = \Request::ip();
		$auditData['device_type'] = 'web';
        //auditTrackRecord($auditData);

        return redirect()->route('cmspages.index')->with('flash_message','Status changed successfully');
	}
}
