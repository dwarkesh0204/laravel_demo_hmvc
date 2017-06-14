<?php

namespace App\Modules\Storesolution\Http\Controllers;

use App\Modules\Storesolution\Models\Store;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Yajra\Datatables\Facades\Datatables;

use Illuminate\Support\Facades\Storage;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        return view('storesolution::stores.index');
    }

    public function StoreData(Request $request)
    {
        $provinces = Store::select('*');

        return Datatables::of($provinces)->make(true);
    }

    public function create()
    {
        return view('storesolution::stores.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $input = $request->all();
        $Store = new Store();
        $Store->name        =  $request->get('name');
        $Store->description =  $request->get('description');
        $Store->venue       =  $request->get('venue');
        $Store->email       =  $request->get('email');
        $Store->phone_no    =  $request->get('phone_no');
        $Store->sections    =  $request->get('sections');
        $Store->status      =  $request->get('status');
        $Store->save();

        $StoreId = $Store->getAttribute('id');

        if($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');

            // Create Directory
            Storage::disk('upload')->makeDirectory('Store/'.$StoreId);

            $storage = public_path()."/uploads/Store/".$StoreId;

            // Create A FileName From Uploaded File.
            $fileName = time().$request->file('cover_image')->getClientOriginalName();

            // Remove Spaces In The Filename And Convert To LowerCase.
            $fileName = str_replace(' ', '', strtolower($fileName));

            // Move The File To Storage Directory
            if($request->file('cover_image')->move($storage,$fileName)){
                $FileNameStore      = "/uploads/Store/".$StoreId."/".$fileName;
                $Store              = Store::find($StoreId);
                $Store->cover_image = $FileNameStore;
                $Store->save();
            }
        }

        return redirect()->route('stores.index')->with('success','Store created successfully');
    }

    public function edit($id)
    {
        $data     = Store::find($id);

        return view('storesolution::stores.edit',compact('data'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $input  = $request->all();
        $stores = Store::find($id)->update($input);

        if($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');

            // Create Directory
            Storage::disk('upload')->makeDirectory('Store/'.$id);

            $storage = public_path()."/uploads/Store/".$id;

            // Create A FileName From Uploaded File.
            $fileName = time().$request->file('cover_image')->getClientOriginalName();

            // Remove Spaces In The Filename And Convert To LowerCase.
            $fileName = str_replace(' ', '', strtolower($fileName));

            // Move The File To Storage Directory
            if($request->file('cover_image')->move($storage,$fileName)){

                $FileNameStore      = "/uploads/Store/".$id."/".$fileName;
                $Store              = Store::find($id);
                $Store->cover_image = $FileNameStore;
                $Store->save();
            }
        }

        return redirect()->route('stores.index')->with('success','Store updated successfully');
    }
    public function destroy(Request $request)
    {
        $Input   = $request->all();
        $IDs     = $Input['IDs'];
        $IDArray = explode(',', $IDs);

        foreach ($IDArray as $key => $Id)
        {
            Store::find($Id)->delete();
        }

        return redirect()->route('stores.index')->with('flash_message','Store deleted successfully');
    }

    /**
     * Delete Image.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteImage(Request $request)
    {
        $Input        = $request->all();
        $StoreId = $Input['id'];
        $Store   = Store::find($StoreId);

        $imagePath = $Store['cover_image'];
        unlink(public_path().$imagePath);
        $Store['cover_image'] = "";
        $Store->save();

        return redirect()->back()->with('flash_message','Image deleted successfully');
    }
}
