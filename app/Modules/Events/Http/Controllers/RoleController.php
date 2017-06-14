<?php
namespace App\Modules\Events\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Modules\Events\Models\Role;
use App\Modules\Events\Models\Permission;
use App\Modules\Events\Models\RoleField;
use App\Modules\Events\Models\PermissionRole;
use Yajra\Datatables\Facades\Datatables;

use DB;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $roles = Role::orderBy('id','DESC')->paginate(5);

        return view('events::roles.index',compact('roles'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    public function RolesData(Request $request)
    {
        $provinces = Role::select('*');

        return Datatables::of($provinces)->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $permission = Permission::get();

        return view('events::roles.create',compact('permission'));
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
            'name'         => 'required',//|unique:roles,name',
            'display_name' => 'required',
            'description'  => 'required',
            'permission'   => 'required'
        ]);

        $role = new Role();
        $role->name         = $request->input('name');
        $role->display_name = $request->input('display_name');
        $role->description  = $request->input('description');
        $role->save();

        foreach ($request->input('permission') as $key => $value) {
            $role->attachPermission($value);
        }

        /*
        $fields = $request->input('fields');
        $data = [];
        if($fields[0]['display_name'])
        {
            foreach ($fields as $key => $value)
            {
                $value['field_name'] = 'field_'.strtolower(str_replace(" ", "_", $value['display_name']));
                $value['role_id']    = $role->id;
                $value['updated_at'] = date('Y-m-d h:i:s');
                $value['created_at'] = date('Y-m-d h:i:s');
                $fields[$key]        = $value;
            }
            RoleField::insert($fields);
        }
        */

        // Store in audit
        $auditData = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'roles';
        $auditData['action']      = 'roles.create';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        //auditTrackRecord($auditData);

        return redirect()->route('roles.index')->with('success','Role created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $role = Role::find($id);
        $rolePermissions = Permission::join("permission_role","permission_role.permission_id","=","permissions.id")
                            ->where("permission_role.role_id",$id)
                            ->get();

        return view('events::roles.show', compact('role','rolePermissions'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $role = Role::find($id);

        $permission = Permission::get();

        $rolePermissions = PermissionRole::where("permission_role.role_id", $id)
            ->pluck('permission_role.permission_id','permission_role.permission_id');

        $rolefields = RoleField::where('id', $id)->get();

        return view('events::roles.edit',compact('role','permission','rolePermissions','rolefields'));
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
            'permission'   => 'required',
        ]);

        $role = Role::find($id);
        $role->display_name = $request->input('display_name');
        $role->description  = $request->input('description');
        $role->save();

        PermissionRole::where("permission_role.role_id",$id)
            ->delete();

        foreach ($request->input('permission') as $key => $value) {
            $role->attachPermission($value);
        }

        /*
        $fields = $request->input('fields');
        foreach ($fields as $key => $value)
        {
            if($value['display_name'])
            {
                if(!empty($value['id']))
                {
                $value['updated_at'] = date('Y-m-d h:i:s');
                    RoleField::where('id', $value['id'])->update(array_except($value,'id'));
                }
                else
                {
                    $value['field_name'] = 'field_'.strtolower(str_replace(" ", "_", $value['display_name']));
                    $value['role_id'] = $role->id;
                    $value['updated_at'] = date('Y-m-d h:i:s');
                    $value['created_at'] = date('Y-m-d h:i:s');
                    RoleField::insert($value);
                }
            }
        }

        $deletedFieldId = $request->input('deletedFieldId');

        if($deletedFieldId)
        {
            $deletedFieldIds = explode(",",$request->input('deletedFieldId'));

            if(count($deletedFieldIds) > 0)
            {
                foreach ($deletedFieldIds as $key => $deletedFieldId)
                {
                    RoleField::find($deletedFieldId)->delete();
                }
            }
        }
        */

        // Store in audit
        $auditData = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'roles';
        $auditData['action']      = 'roles.edit';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        //auditTrackRecord($auditData);

        return redirect()->route('roles.index')->with('success','Role updated successfully');
    }

    /**

     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {
        Role::where('id',$id)->delete();

        // Store in audit
        $auditData = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'roles';
        $auditData['action']      = 'roles.delete';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        //auditTrackRecord($auditData);

        return redirect()->route('roles.index')->with('success','Role deleted successfully');

    }

}