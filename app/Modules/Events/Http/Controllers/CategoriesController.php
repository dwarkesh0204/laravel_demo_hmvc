<?php

namespace App\Modules\Events\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Modules\Events\Http\Requests;

use App\Modules\Events\Models\Categories;

use Yajra\Datatables\Facades\Datatables;

use Carbon\Carbon;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = $this->fetchCategoryTree();

        return view('events::category.index',compact('items'));
    }

    public function CategoriesData(Request $request)
    {
        $provinces = Categories::select('*');

        return Datatables::of($provinces)->make(true);
    }

    function fetchCategoryTree($parent = 0, $spacing = '', $user_tree_array = '')
    {
        if (!is_array($user_tree_array))
        {
            $user_tree_array = array();
        }
        else
        {
            $spacing .= '-';
        }

        $items = Categories::Where('parent',$parent)->orderBy('id','ASC')->paginate();

        if (count($items) > 0)
        {
            foreach ($items as $key => $value)
            {
                $user_tree_array[] = array("id" => $value->id, "title" => $spacing . '&nbsp;' . $value->title, "parent" => $value->parent, "status" => $value->status, "created_at" => $value->created_at, "updated_at" => $value->updated_at);
                $user_tree_array = $this->fetchCategoryTree($value->id, $spacing . '&nbsp;', $user_tree_array);
            }
        }
        return $user_tree_array;
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

        $items = Categories::Select('id','title')->Where('parent',$parent)->orderBy('id','ASC')->paginate();

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
        //$Categories     = Categories::where('status', 1)->orderBy('id','DESC')->lists('title', 'id')->toArray();
        $Categories = $this->fetchCategoryTreeForOptions();
        return view('events::category.create',compact('Categories'));
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
            'title' => 'required'
        ]);

        $category = new Categories();
        $category->title       = $request->title;
        $category->description = $request->description;
        $category->status      = $request->status;
        $category->created_at  = Carbon::now();
        $category->updated_at  = Carbon::now();
        $category->save();

        return redirect()->route('category.index')->with('flash_message','Category created successfully');
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
        $item          = Categories::find($id);
        //$Categories    = Categories::where('status', 1)->orderBy('id','DESC')->lists('title', 'id')->toArray();
        $Categories    = $this->fetchCategoryTreeForOptions();

        return view('events::category.edit',compact('item','Categories'));
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
            'title' => 'required'
        ]);

        $input = $request->all();
        Categories::find($id)->update($input);

        return redirect()->route('category.index')->with('flash_message','Category updated successfully');
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
            Categories::find($catId)->delete();
            //$Menu      = Menu::find($menuId)->delete();
        }

        return redirect()->route('category.index')->with('flash_message','Categories deleted successfully');
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
        $Categories = Categories::find($id);

        if ($statusMode == 0){
            $Categories->status = 0;
        }else{
            $Categories->status = 1;
        }
        $Categories->save();

        return redirect()->route('category.index')->with('flash_message','Status changed successfully');
    }
}
