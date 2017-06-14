<?php

namespace App\Modules\Events\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Modules\Events\Http\Requests;

use App\Modules\Events\Models\Menu;

use App\Modules\Events\Models\Cmspage;

use Illuminate\Support\Facades\Storage;

use Intervention\Image\ImageManagerStatic as Image;

use File;

use App\Modules\Events\Models\Categories;

use Yajra\Datatables\Facades\Datatables;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = menu::orderBy('order','ASC')->paginate();

        return view('events::menu.index',compact('items'));
    }

    public function MenuData(Request $request)
    {
        $provinces = Menu::select('*');

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
        $cmsPages    = Cmspage::where('active', 1)->orderBy('id','DESC')->pluck('display_name', 'id')->toArray();
        $cmsPages[0] = 'select';
        $cmsPages    =  array_reverse($cmsPages, true);

        $parentMenus    = Menu::where('status', 1)->orderBy('id','DESC')->pluck('title', 'id')->toArray();
        $parentMenus[0] = 'Main Menu';
        $parentMenus    =  array_reverse($parentMenus, true);

        $Categories    = $this->fetchCategoryTreeForOptions();

        return view('events::menu.create',compact('cmsPages','parentMenus','Categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $max_order    = Menu::max('order');
        $default_page = Menu::select('id')->where('default_page',1)->first();

        //dd($request->all());die;
        if(isset($default_page->id) == '')
        {
            $this->validate($request, [
                'default_page' => 'required',
                'title'        => 'required',
                'view_name'    => 'required',
                'screens'      => 'required',
                'icon'         => 'dimensions:min_width=192,min_height=192'
            ]);
        }
        else
        {
            $this->validate($request, [
                'title'     => 'required',
                'view_name' => 'required',
                'screens'   => 'required',
                'icon'      => 'dimensions:min_width=192,min_height=192'
            ]);
        }

        $Input = $request->all();

        if(!isset($Input['default_page']))
        {
           $Input['default_page'] = 0;
        }

        if($Input['default_page'] == 1 && isset($default_page->id))
        {
            $menuData = Menu::find($default_page->id);
            $menuData->default_page = 0;
            $menuData->save();
        }

        $screens          = $Input['screens'];
        $Input['screens'] = implode(",",$screens);
        $Input['order']   = $max_order + 1;

        $menu = new Menu();
        $menu->title           = $request->title;
        $menu->descriptions    = $request->descriptions;
        $menu->view_name       = $request->view_name;
        $menu->screens         = $Input['screens'];
        $menu->order           = $Input['order'];
        $menu->parent          = $request->parent;
        $menu->cat_id          = $request->cat_id;
        $menu->display_icon    = $request->display_icon;
        $menu->default_page    = $request->default_page;
        $menu->login_requuired = $request->login_requuired;
        $menu->status          = $request->status;
        $menu->extra_data      = $request->extra_data;
        $menu->cmsPage         = $request->cmsPage;
        $menu->webLink         = $request->webLink;
        $menu->HTMLContent     = $request->HTMLContent;
        $menu->webLink         = $request->webLink;
        $menu->save();

        $menuId = $menu->getAttribute('id');

        if($request->hasFile('icon'))
        {
            $file = $request->file('icon');

            // Create Directory
            Storage::disk('upload')->makeDirectory('Menus/'.$menuId);

            $storage = public_path()."/uploads/Menus/".$menuId;

            // Create A FileName From Uploaded File.
            //$fileName = time().$request->file('icon')->getClientOriginalName();
            $extension = $request->file('icon')->getClientOriginalExtension();
            $fileName = $Input['view_name'].'.'.$extension;

            // Remove Spaces In The Filename And Convert To LowerCase.
            $fileName = str_replace(' ', '', strtolower($fileName));

            // Move The File To Storage Directory
            if($request->file('icon')->move($storage,$fileName))
            {
                $orignleFilePath = "/uploads/Menus/".$menuId."/".$fileName;
                $FileNameStore = $fileName;
                $menu          = Menu::find($menuId);
                $menu->icon    = $FileNameStore;
                $menu->save();
                $this->resizeIcons($orignleFilePath, $menuId, $fileName);
            }
        }

        // Store in audit
        $auditData = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'menu';
        $auditData['action']      = 'menu.create';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);

        return redirect()->route('menu.index')->with('flash_message','Menu created successfully');
    }

    /**
     * Resize Icons for android and ios with all device size
     */

    public function resizeIcons($orignleFilePath, $menuId, $fileName)
    {
        $img = Image::make(asset($orignleFilePath));

        // For Android Icon Resize as device size.
        $xxxhdpi = public_path('uploads/Menus/'.$menuId.'/xxxhdpi_'.$fileName);
        $img->resize(192, 192);
        $img->save($xxxhdpi);

        $xxhdpi = public_path('uploads/Menus/'.$menuId.'/xxhdpi_'.$fileName);
        $img->resize(144, 144);
        $img->save($xxhdpi);

        $xhdpi = public_path('uploads/Menus/'.$menuId.'/xhdpi_'.$fileName);
        $img->resize(96, 96);
        $img->save($xhdpi);

        $hdpi = public_path('uploads/Menus/'.$menuId.'/hdpi_'.$fileName);
        $img->resize(72, 72);
        $img->save($hdpi);

        $mdpi = public_path('uploads/Menus/'.$menuId.'/mdpi_'.$fileName);
        $img->resize(48, 48);
        $img->save($mdpi);

        // For Ios Icon Resize as device size.
        $oneX = public_path('uploads/Menus/'.$menuId.'/x_'.$fileName);
        $img->resize(32, 32);
        $img->save($oneX);

        $twoX = public_path('uploads/Menus/'.$menuId.'/2x_'.$fileName);
        $img->resize(64, 64);
        $img->save($twoX);

        $ThreeX = public_path('uploads/Menus/'.$menuId.'/3x_'.$fileName);
        $img->resize(96, 96);
        $img->save($ThreeX);

        return true;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item = Menu::find($id);
        $screens     = explode(',',$item->screens);
        $cmsPages    = Cmspage::where('active', 1)->orderBy('id')->pluck('display_name', 'id')->toArray();
        $cmsPages[0] = 'select';
        $cmsPages    =  array_reverse($cmsPages, true);

        $parentMenus    = Menu::where('status', 1)->orderBy('id','DESC')->pluck('title', 'id')->toArray();
        $parentMenus[0] = 'Main Menu';
        $parentMenus    = array_reverse($parentMenus, true);

        $Categories    = $this->fetchCategoryTreeForOptions();

        return view('events::menu.edit',compact('item','screens','cmsPages','parentMenus','Categories'));
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
        $default_page = Menu::select('id')->where('default_page',1)->first();
        $this->validate($request, [
            'title'     => 'required',
            'view_name' => 'required',
            'screens'   => 'required',
            'icon'      => 'dimensions:min_width=192,min_height=192'
        ]);

        $input            = $request->all();

        if($input['default_page'] == 1 && isset($default_page->id))
        {
            $menuData = Menu::find($default_page->id);
            $menuData->default_page = 0;
            $menuData->save();
        }

        $screens          = $input['screens'];
        $input['screens'] = implode(",",$screens);
        Menu::find($id)->update($input);
        $menuId = $id;

        if($request->hasFile('icon'))
        {
            $file = $request->file('icon');

            // Create Directory
            Storage::disk('upload')->makeDirectory('Menus/'.$menuId);

            $storage = public_path()."/uploads/Menus/".$menuId;

            // Create A FileName From Uploaded File.
            //$fileName = time().$request->file('icon')->getClientOriginalName();
            $extension = $request->file('icon')->getClientOriginalExtension();
            $fileName = $input['view_name'].'.'.$extension;

            // Remove Spaces In The Filename And Convert To LowerCase.
            $fileName = str_replace(' ', '', strtolower($fileName));

            // Move The File To Storage Directory
            if($request->file('icon')->move($storage,$fileName))
            {
                $orignleFilePath = "/uploads/Menus/".$menuId."/".$fileName;
                $FileNameStore = $fileName;
                $menu          = Menu::find($menuId);
                $menu->icon    = $FileNameStore;
                $menu->save();
                $this->resizeIcons($orignleFilePath, $menuId, $fileName);
            }
        }

        // Store in audit
        $auditData = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'menu';
        $auditData['action']      = 'menu.edit';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);

        return redirect()->route('menu.index')->with('flash_message','Menu updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $Input       = $request->all();
        $menuIDs     = $Input['MenuIDs'];
        $menuIdArray = explode(',', $menuIDs);

        foreach ($menuIdArray as $key => $menuId)
        {
            $Menu      = Menu::find($menuId)->delete();
            $directory = "/uploads/Menus/".$menuId.'/';
            File::deleteDirectory(public_path().$directory);

            // Store in audit
            $auditData = array();
            $auditData['user_id']     = \Auth::user()->id;
            $auditData['section']     = 'menu';
            $auditData['action']      = 'menu.delete';
            $auditData['time_stamp']  = time();
            $auditData['device_id']   = \Request::ip();
            $auditData['device_type'] = 'web';
            auditTrackRecord($auditData);

        }

        return redirect()->route('menu.index')->with('flash_message','Item deleted successfully');
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
        $Menu    = menu::find($id);

        if ($statusMode == 0){
            $Menu->status = 0;
        }else{
            $Menu->status = 1;
        }
        $Menu->save();

        // Store in audit
        $auditData = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'menu';
        $auditData['action']      = 'menu.changeStatus';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);

        return redirect()->route('menu.index')->with('flash_message','Status changed successfully');
    }

    public function saveOrder(Request $request)
    {
        $input        = $request->all();
        $orderData    = $input['orderData'];
        $orderDatas   = explode(',',$orderData);
        $removedFirst = array_shift($orderDatas);

        foreach ($orderDatas as $key => $value)
        {
            $valofArr    = explode(":",$value);
            $orderVal    = $valofArr[0];
            $menuId      = $valofArr[1];
            $Menu        = Menu::find($menuId);
            $Menu->order = $orderVal;
            $Menu->save();
        }
        $result  = 1;
        $message = "success";
        return \Response::json(['success'=>$result,'message'=>$message]);
    }
}
