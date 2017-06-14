<?php

namespace App\Modules\Events\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Modules\Events\Http\Requests;

use App\Modules\Events\Models\Fields;

use App\Modules\Events\Models\Events;

use App\Modules\Events\Models\Role;

use App\Modules\Events\Models\FieldsRelation;

use Carbon\Carbon;

class FieldsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = Fields::orderBy('group_id','ASC')->where('group_id', '!=' , '')->paginate();
        foreach ($items as $key => $value)
        {
            if($value['group_id'])
            {
                $group_nameVal     = Fields::select('display_name')->where('id', $value['group_id'])->get();
                $group_name        = json_decode($group_nameVal);
                $value['group_name'] = $group_name[0]->display_name;
            }
        }
        return view('events::fields.index',compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $groups    = Fields::where('active', 1)->where('group_id', 0)->orderBy('id','DESC')->pluck('display_name', 'id')->toArray();
        $groups[0] = 'select';
        $groups    =  array_reverse($groups, true);

        $events    =  Events::where('status', 1)->orderBy('eventid','DESC')->pluck('name', 'eventid')->toArray();
        $events[''] =  'select';
        $events    =  array_reverse($events, true);

        $roles     =  Role::orderBy('id','DESC')->pluck('display_name', 'id')->toArray();
        $roles['']  =  'select';
        $roles     =  array_reverse($roles, true);

        return view('events::fields.create', compact('groups','events','roles'));
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
            'field_type'   => 'required',
            'group_id'     => 'required|integer|min:1'
        ]);

        if($request->role_id[0] == '')
        {
            $this->validate($request, [
                'role_id.*'      => 'required'
            ]);
        }

        if($request->event_id[0] == '')
        {
            $this->validate($request, [
                'event_id.*'      => 'required'
            ]);
        }

        $input   = $request->all();

        //$fields  = Fields::create($input);
        $fields = new Fields();
        $fields->display_name    = $request->display_name;
        $fields->field_name      = $request->field_name;
        $fields->field_type      = $request->field_type;
        $fields->items           = $request->items;
        $fields->default         = $request->default;
        $fields->validation_rule = $request->validation_rule;
        $fields->multiple        = $request->multiple;
        $fields->required        = $request->required;
        $fields->searchable      = $request->searchable;
        $fields->filterable      = $request->filterable;
        $fields->privacy         = $request->privacy;
        $fields->type            = $request->type;
        $fields->group_id        = $request->group_id;
        $fields->active          = $request->active;
        $fields->created_at      = Carbon::now();
        $fields->updated_at      = Carbon::now();
        $fields->save();

        $fieldId = $fields->getAttribute('id');

        if($fieldId && ($input['type'] == 'events' || $input['type'] == 'roles'))
        {
            if(!empty($input['event_id']))
            {
                foreach ($input['event_id'] as $key => $eventId)
                {
                    $fieldsRelation = new FieldsRelation();
                    $fieldsRelation->field_id   = $fieldId;
                    $fieldsRelation->role_id    = 0;
                    $fieldsRelation->event_id   = $eventId;
                    $fieldsRelation->created_at = Carbon::now();
                    $fieldsRelation->updated_at = Carbon::now();
                }
            }

            if(!empty($input['role_id']))
            {
                foreach ($input['role_id'] as $key => $roleId)
                {
                    $fieldsRelation = new FieldsRelation();
                    $fieldsRelation->field_id   = $fieldId;
                    $fieldsRelation->role_id    = $roleId;
                    $fieldsRelation->event_id   = 0;
                    $fieldsRelation->created_at = Carbon::now();
                    $fieldsRelation->updated_at = Carbon::now();
                }
            }
        }

        // Store in audit
        $auditData = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'formFields';
        $auditData['action']      = 'formFields.create';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        //auditTrackRecord($auditData);

        return redirect()->route('fields.index')->with('flash_message','Field created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item            = Fields::find($id);
        $field_relations = FieldsRelation::where('field_id',$id)->get();
        $event_id        = array(1);
        $role_id         = array();

        foreach ($field_relations as $key => $field_relation)
        {
            if(!empty($field_relation['event_id']))
            {
                $event_id[] = $field_relation['event_id'];
            }

            if(!empty($field_relation['role_id']))
            {
                $role_id[] = $field_relation['role_id'];
            }
        }

        //$item = Fields::select('fields_relation.id as frid','fields.*','fields_relation.role_id','fields_relation.event_id','fields_relation.field_id')->join('fields_relation', 'fields.id', '=', 'fields_relation.field_id')->where('fields.id',$id)->first();

        $groups    = Fields::where('active', 1)->where('group_id', 0)->orderBy('id','DESC')->pluck('display_name', 'id')->toArray();
        $groups[0] = 'select';
        $groups    =  array_reverse($groups, true);

        $events    =  Events::where('status', 1)->orderBy('eventid','DESC')->pluck('name', 'eventid')->toArray();
        $events[''] =  'select';
        $events    =  array_reverse($events, true);

        $roles     =  Role::orderBy('id','DESC')->pluck('display_name', 'id')->toArray();
        $roles['']  =  'select';
        $roles     =  array_reverse($roles, true);

        return view('events::fields.edit',compact('item','groups','events', 'roles','event_id','role_id'));
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
            'field_type'   => 'required',
            'group_id'     => 'required|integer|min:1'
        ]);

        if($request->role_id[0] == '')
        {
            $this->validate($request, [
                'role_id.*'      => 'required'
            ]);
        }

        if($request->event_id[0] == '')
        {
            $this->validate($request, [
                'event_id.*'      => 'required'
            ]);
        }

        $input = $request->all();

        Fields::find($id)->update($request->all());

        if($id && $input['type'] == 'events')
        {
            if(!empty($input['event_id']))
            {
                $field_relations = FieldsRelation::where('field_id', $id)->get();

                foreach ($field_relations as $key => $field_relation)
                {
                    FieldsRelation::find($field_relation->id)->delete();
                }

                foreach ($input['event_id'] as $key => $event_id)
                {
                    $fieldsRelation = new FieldsRelation();
                    $fieldsRelation->field_id   = $fieldId;
                    $fieldsRelation->role_id    = 0;
                    $fieldsRelation->event_id   = $eventId;
                    $fieldsRelation->created_at = Carbon::now();
                    $fieldsRelation->updated_at = Carbon::now();
                }
            }
        }

        if($id && $input['type'] == 'roles')
        {
            if(!empty($input['role_id']))
            {
                $field_relations = FieldsRelation::where('field_id', $id)->get();

                foreach ($field_relations as $key => $field_relation)
                {
                    FieldsRelation::find($field_relation->id)->delete();
                }

                foreach ($input['role_id'] as $key => $role_id)
                {
                    $fieldsRelation = new FieldsRelation();
                    $fieldsRelation->field_id   = $fieldId;
                    $fieldsRelation->role_id    = $roleId;
                    $fieldsRelation->event_id   = 0;
                    $fieldsRelation->created_at = Carbon::now();
                    $fieldsRelation->updated_at = Carbon::now();
                }
            }
        }

        if($id && $input['type'] == 'register')
        {
            $field_relations = FieldsRelation::where('field_id', $id)->get();
            foreach ($field_relations as $key => $field_relation)
            {
                FieldsRelation::find($field_relation->id)->delete();
            }
        }

        // Store in audit
        $auditData = array();
        $auditData['user_id'] = \Auth::user()->id;
        $auditData['section'] = 'formFields';
        $auditData['action'] = 'formFields.edit';
        $auditData['time_stamp'] = time();
        $auditData['device_id'] = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);

        return redirect()->route('fields.index')->with('flash_message','Field updated successfully');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $Input         = $request->all();
        $pagesIDs      = $Input['fieldsIDs'];
        $fieldsIdArray = explode(',', $pagesIDs);




        foreach ($fieldsIdArray as $key => $fieldId)
        {

            $subFields = Fields::where('group_id', $fieldId)->first();

            if($subFields)
            {
                if($subFields->id)
                {
                    return redirect()->route('fields.index')->with('delete_message',"You can not delete this group, It's contain some fields");
                }
            }

            Fields::find($fieldId)->delete();

            // Store in audit
            $auditData                = array();
            $auditData['user_id']     = \Auth::user()->id;
            $auditData['section']     = 'formFields';
            $auditData['action']      = 'formFields.delete';
            $auditData['time_stamp']  = time();
            $auditData['device_id']   = \Request::ip();
            $auditData['device_type'] = 'web';
            auditTrackRecord($auditData);
        }
        return redirect()->route('fields.index')->with('flash_message','Field deleted successfully');
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
        $Field      = Fields::find($id);

        if ($statusMode == 'unpublish'){
            $Field->active = 0;
        }else{
            $Field->active = 1;
        }
        $Field->save();

        // Store in audit
        $auditData                = array();
        $auditData['user_id']     = \Auth::user()->id;
        $auditData['section']     = 'formFields';
        $auditData['action']      = 'formFields.changeStatus';
        $auditData['time_stamp']  = time();
        $auditData['device_id']   = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);

        return redirect()->route('fields.index')->with('flash_message','Status changed  successfully');
    }

    public function storeGroup(Request $request)
    {
        $this->validate($request, [
            'display_name' => 'required'
        ]);

        $input = $request->all();

        if($input['task'] == 'update')
        {
            $group_id = $input['group_id'];
            unset($input['_token']);
            unset($input['group_id']);
            unset($input['field_type']);
            unset($input['task']);
            $groupDetail = Fields::where('id', $group_id)->update($input);
            $action = 'formFields.group.update';
            $message = "Group Updated Successfully";


        }
        else
        {
            Fields::create($input);
            $action = 'formFields.group.create';
            $message = "Group Created Successfully";

            return redirect()->route('fields.index')->with('flash_message','Group Created successfully');
        }

        //Fields::create($input);

        // Store in audit
        $auditData = array();
        $auditData['user_id'] = \Auth::user()->id;
        $auditData['section'] = 'formFields';
        $auditData['action']  = $action;
        $auditData['time_stamp'] = time();
        $auditData['device_id'] = \Request::ip();
        $auditData['device_type'] = 'web';
        auditTrackRecord($auditData);

        return redirect()->route('fields.index')->with('flash_message', $message);
    }

    public function getGroupDetail(Request $request)
    {
        $Input                = $request->all();
        $group_id             = $Input['group_id'];
        $FieldsDetail         = Fields::find($group_id);
        $data                 = array();
        $data['id']           = $FieldsDetail->id;
        $data['display_name'] = $FieldsDetail->display_name;
        $data['active']       = $FieldsDetail->active;
        $data['task']         = 'update';

        $result               = 1;
        return \Response::json(['success'=>$result,'data'=>$data]);
    }
}
