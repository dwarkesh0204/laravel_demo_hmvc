@extends('events::layouts.app')

<style type="text/css">
img.img-responsive {
  filter: gray; /* IE6-9 */
  -webkit-filter: grayscale(1); /* Google Chrome, Safari 6+ & Opera 15+ */
    -webkit-box-shadow: 0px 2px 6px 2px rgba(0,0,0,0.75);
    -moz-box-shadow: 0px 2px 6px 2px rgba(0,0,0,0.75);
    box-shadow: 0px 2px 6px 2px rgba(0,0,0,0.75);
    margin-bottom:20px;
}

img:hover {
  filter: none; /* IE6-9 */
  -webkit-filter: grayscale(0); /* Google Chrome, Safari 6+ & Opera 15+ */

}
.edit {
    padding-top: 7px;
    padding-right: 15px;
    position: absolute;
    right: 0;
    top: 0;
    color: #FFFFFF;

}

.edit a {
    color: #000;
}
img:hover .edit {
    display: block !important;

}

</style>

@section('content')
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCa2HEyzmSepTQnzX9deSUhBj_G9x9cSpc&libraries=geometry,places&location=no"></script>
<div class="container">
    <div class="row">
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        @if(Session::has('flash_message'))
            <div class="alert alert-success">
                {{ Session::get('flash_message') }}
            </div>
        @endif

        <div class="panel panel-default">
            {!! Form::model($item, ['name'=> 'updateEvent', 'id' => 'updateEvent', 'method' => 'PATCH','files' => 'true','route' => ['events.update', $item->eventid]]) !!}
            <div class="panel-heading">
                <div>
                    <ul id="tab" class="nav nav-tabs" role="tablist">
                        <li class="active" >
                            <a href="#event_detail" aria-controls="event_detail" role="tab" data-toggle="tab">
                                <strong>Event</strong>
                            </a>
                        </li>
                       <!--  <li>
                           <a href="#event_fields" aria-controls="event_fields" role="tab" data-toggle="tab">
                               <strong>Event Fields</strong>
                           </a>
                       </li> -->
                        <li>
                            <a href="#event_images" aria-controls="event_images" role="tab" data-toggle="tab">
                                <strong>Event Images</strong>
                            </a>
                        </li>
                        <li>
                            <a href="#event_videos" aria-controls="event_videos" role="tab" data-toggle="tab">
                                <strong>Event Videos</strong>
                            </a>
                        </li>
                        <li>
                            <a href="#event_documents" aria-controls="event_documents" role="tab" data-toggle="tab">
                                <strong>Event Document</strong>
                            </a>
                        </li>
                        <li>
                            <a href="#event_tickets" aria-controls="event_tickets" role="tab" data-toggle="tab">
                                <strong>Event Tickets</strong>
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="event_detail">
                            <div class="box-body">
                                <div class="form-group row">
                                    {{Form::label('name', 'Title:*', ['class' => 'col-md-2 col-form-label'])}}
                                    <div class="col-md-5">
                                        {{Form::text('name',null,array('class' => 'form-control'))}}
                                    </div>
                                </div>

                                <div class="form-group row">
                                    {{Form::label('shortdesc', 'Short Description:', ['class' => 'col-md-2 col-form-label'])}}
                                    <div class="col-md-10">
                                        {{Form::textarea('shortdesc',null,array('class' => 'form-control', 'rows' => '4', 'cols'=> '50'))}}
                                    </div>
                                </div>

                                <div class="form-group row">
                                    {{Form::label('description', 'Description:', ['class' => 'col-md-2 col-form-label'])}}
                                    <div class="col-md-10">
                                        {{Form::textarea('description',null,array('class' => 'form-control', 'rows' => '8', 'cols'=> '100'))}}
                                    </div>
                                </div>

                                <div class="form-group row">
                                    {{Form::label('status', 'Active:', ['class' => 'col-md-2 col-form-label'])}}
                                    <div class="form-inline col-md-3">
                                        <div class="radio">
                                            {{ Form::radio('status', '1', true)}}
                                            {{ Form::label('Yes', 'Yes')}}
                                        </div>
                                        <div class="radio">
                                            {{ Form::radio('status', '0', false)}}
                                            {{ Form::label('No', 'No') }}
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    {{Form::label('startdate', 'Start Date*:', ['class' => 'col-md-2 col-form-label'])}}
                                    <div class="col-md-3">
                                        {{Form::text('startdate', null, ['class' => 'form-control', 'id' => 'start_date'])}}
                                    </div>
                                    <div class="col-md-2">
                                        {{Form::text('starttime', null, ['class' => 'form-control', 'id' => 'start_time'])}}
                                    </div>
                                </div>

                                <div class="form-group row">
                                    {{Form::label('enddate', 'End Date*:', ['class' => 'col-md-2 col-form-label'])}}
                                    <div class="col-md-3">
                                        {{Form::text('enddate', null, ['class' => 'form-control', 'id' => 'end_date'])}}
                                    </div>
                                    <div class="col-md-2">
                                        {{Form::text('endtime', null, ['class' => 'form-control', 'id' => 'end_time'])}}
                                    </div>
                                </div>

                                <div class="form-group row">
                                    {{Form::label('cover_image', 'Cover Photo:', ['class' => 'col-md-2 col-form-label'])}}
                                    <div class="col-md-3">
                                        {{Form::file('cover_image')}}
                                    </div>
                                    @if(!empty($item->cover_image))
                                        <div class="col-md-4">
                                            {{Html::image(asset($item->cover_image), $item->cover_image, ['class' => 'cover-image', 'height' => '150px', 'width'=> '200px'])}}
                                            <span class="glyphicon glyphicon-remove remove-cover" aria-hidden="false" style="color: red; cursor: pointer; margin-left: -20px; position: absolute; font-size: 20px;"></span>
                                        </div>
                                    @endif
                                </div>

                                <div class="form-group row">
                                    {{Form::label('type', 'Type:', ['class' => 'col-md-2 col-form-label'])}}
                                    <div class="form-inline col-md-3">
                                        <div class="radio">
                                            {{ Form::radio('type', 'inviteonly', true)}}
                                            {{ Form::label('Inviteonly', 'Inviteonly')}}
                                        </div>
                                        <div class="radio">
                                            {{ Form::radio('type', 'open', false)}}
                                            {{ Form::label('Open', 'Open') }}
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    {{Form::label('price', 'Price:', ['class' => 'col-md-2 col-form-label'])}}
                                    <div class="col-md-3">
                                        {{Form::text('price', null, ['class' => 'form-control'])}}
                                    </div>
                                </div>

                                <div class="form-group row">
                                    {{Form::label('expected_attendaees', 'Expected Attendaees:', ['class' => 'col-md-2 col-form-label'])}}
                                    <div class="col-md-3">
                                        {{Form::text('expected_attendaees', null, ['class' => 'form-control'])}}
                                    </div>
                                </div>

                                <div class="form-group row">
                                    {{Form::label('venue', 'Venue Address:', ['class' => 'col-md-2 col-form-label'])}}
                                    <div class="col-md-5">
                                        {{Form::text('venue', null, ['class' => 'form-control', 'id' => 'venue_address', 'placeholder' => 'Enter Venue address'])}}
                                    </div>
                                </div>

                                <div class="form-group row">
                                    {{Form::label('phone_number', 'Phone Number:', ['class' => 'col-md-2 col-form-label'])}}
                                    <div class="col-md-3">
                                        {{Form::text('phone_number', null, ['class' => 'form-control'])}}
                                    </div>
                                </div>

                                <div class="form-group row">
                                    {{Form::label('email', 'Email:', ['class' => 'col-md-2 col-form-label'])}}
                                    <div class="col-md-3">
                                        {{Form::text('email', null, ['class' => 'form-control'])}}
                                    </div>
                                </div>

                                <div class="form-group row">
                                    {{Form::label('entrypass', 'Entry Pass:', ['class' => 'col-md-2 col-form-label'])}}
                                    <div class="col-md-5">
                                        {{ Form::checkbox('entrypass', '1')}}
                                        Required
                                    </div>
                                </div>

                                <div class="form-group row">
                                    {{Form::label('pagetype', 'Type:', ['class' => 'col-md-2 col-form-label'])}}
                                    <div class="form-inline col-md-3">
                                        <div class="radio pagetype">
                                            {{ Form::radio('pagetype', 'url', true)}}
                                            {{ Form::label('Url', 'Url') }}
                                        </div>
                                        <div class="radio pagetype">
                                            {{ Form::radio('pagetype', 'html', false)}}
                                            {{ Form::label('Html', 'Html')}}
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row page-url">
                                    {{Form::label('url', 'Cms Page Url:', ['class' => 'col-md-2 col-form-label'])}}
                                    <div class="col-md-5">
                                        {{Form::url('url',null,array('class' => 'form-control'))}}
                                    </div>
                                </div>

                                <div class="form-group page-html" style="display:none;">
                                    {{Form::label('html', 'Html Content:')}}
                                    {{Form::textarea('html',null,array('class' => 'form-control','id' => 'technig'))}}
                                </div>

                                <div class="form-group row meals">
                                    {{Form::label('meals', 'Meal Type:', ['class' => 'col-md-2 col-form-label'])}}
                                    <div class="form-inline col-md-3">
                                        <div class="radio">
                                            {{ Form::radio('meals', 'Free', true)}}
                                            {{ Form::label('Free', 'Free')}}
                                        </div>
                                        <div class="radio">
                                            {{ Form::radio('meals', 'Paid', false)}}
                                            {{ Form::label('Paid', 'Paid') }}
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <strong>Roles:</strong>
                                    <br/>
                                    @foreach($roles as $value)
                                            <label>{{ Form::checkbox('roles[]', $value->id, in_array($value->id, $eventRoles) ? true : false, array('class' => 'name')) }}
                                            {{ $value->name }}</label>
                                        <br/>
                                    @endforeach
                                </div>

                                <div class="form-group">
                                    {{Form::hidden('latitude', null, ['id' => 'latitude'])}}
                                    {{Form::hidden('longitude', null, ['id' => 'longitude'])}}
                                    <!-- <div class="pull-right">
                                        <a href="javascript:void(0)" class="btn btn-primary btnNext">Next</a>
                                    </div> -->
                                </div>

                                <div class="col-lg-12">
                                    <div class="pull-right">
                                        {{Form::submit('Submit',array('class' => 'btn btn-primary'))}}
                                        <a href="{{ route('events.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
                                    </div>
                                </div>
                                {!! Form::close() !!}
                            </div>
                        </div>

                        <div role="tabpanel" class="tab-pane" id="event_fields">
                            <div class="row clearfix">
                                <div class="col-md-12 column">
                                    <table class="table table-bordered table-hover" id="tab_logic">
                                        <p id="count" style="display:none">{{ count($eventfields) }}</p>
                                        <tbody>
                                           @if(count($eventfields))
                                           @foreach($eventfields as $key => $eventfield)
                                                {{Form::hidden('fields['.$key.'][id]',$eventfield->id,array('id' => 'fields['.$key.'][id]'))}}
                                                <tr id='addr{{ $key }}' >
                                                    <td >
                                                    {{$key}}
                                                    </td>

                                                    <td>
                                                        {{Form::label('display_name', 'Caption*:')}}
                                                        {{Form::text('fields['.$key.'][display_name]',$eventfield->display_name,array('class' => 'form-control'))}}
                                                    </td>

                                                    <td>
                                                        {!! Form::label('field_type', 'Select Type*:', ['class' => 'control-label']) !!}
                                                        {!! Form::select('fields['.$key.'][field_type]',
                                            array('' => 'Select', 'text' => 'Textbox', 'textarea' => 'Textarea', 'dropdown' => 'Dropdown', 'checkbox' => 'Checkbox Group', 'radio' => 'Radio Group', 'password' => 'Password', 'file' => 'File Upload','email' => 'Email Address'), $eventfield->field_type, ['class' => 'form-control field_type']) !!}
                                                    </td>
                                                    @if($eventfield->field_type == 'dropdown' || $eventfield->field_type == 'checkbox' || $eventfield->field_type == 'radio')
                                                    <td class="items">
                                                    {{Form::label('items', 'Items:')}}
                                                    {{Form::text('fields['.$key.'][items]',$eventfield->items, array('class' => 'form-control'))}}
                                                    </td>
                                                    @else
                                                    <td class="items" style="display: none">
                                                    {{Form::label('items', 'Items:')}}
                                                    {{Form::text('fields['.$key.'][items]',null ,array('class' => 'form-control'))}}
                                                    </td>
                                                    @endif

                                                    @if($eventfield->field_type == 'dropdown' || $eventfield->field_type == 'checkbox' || $eventfield->field_type == 'radio')
                                                    <td class="multiple">
                                                    {{Form::label('multiple', 'Multiple:')}}
                                                    {{ Form::radio('fields['.$key.'][multiple]', '1', $eventfield->multiple == 1 ? 'ture' : '')}}
                                                    {{ Form::label('Yes', 'Yes')}}
                                                    {{ Form::radio('fields['.$key.'][multiple]', '0', $eventfield->multiple == 0 ? 'false' : '')}}
                                                    {{ Form::label('No', 'No') }}
                                                    </td>
                                                    @else
                                                    <td class="multiple" style="display: none">
                                                    {{Form::label('multiple', 'Multiple:')}}
                                                    {{ Form::radio('fields['.$key.'][multiple]', '1', 'false')}}
                                                    {{ Form::label('Yes', 'Yes')}}
                                                    {{ Form::radio('fields['.$key.'][multiple]', '0', 'true')}}
                                                    {{ Form::label('No', 'No') }}
                                                    </td>
                                                    @endif

                                                    <td colspan="2">
                                                    {{Form::label('active', 'Active:')}}
                                                    {{ Form::radio('fields['.$key.'][active]', '1', $eventfield->active == 1 ? 'ture' : '')}}
                                                    {{ Form::label('Yes', 'Yes')}}
                                                    {{ Form::radio('fields['.$key.'][active]', '0', $eventfield->active == 0 ? 'false' : '')}}
                                                    {{ Form::label('No', 'No') }}
                                                    </td>

                                                    <td colspan="3">
                                                    {{Form::label('required', 'Required:')}}
                                                    {{ Form::radio('fields['.$key.'][required]','1', $eventfield->required === '1' ? 'true' : 'false')}}
                                                    {{ Form::label('Yes', 'Yes')}}
                                                    {{ Form::radio('fields['.$key.'][required]','0', $eventfield->required === '0' ? 'false' : 'true')}}
                                                    {{ Form::label('No', 'No') }}
                                                    </td>
                                                </tr>
                                                @endforeach
                                                <tr id='addr{{$key+1}}'></tr>
                                                @else
                                                <tr id='addr0'>
                                                    <td>1</td>
                                                    <td>
                                                        {{Form::label('display_name', 'Caption*:')}}
                                                        {{Form::text('fields[0][display_name]',null,array('class' => 'form-control'))}}
                                                    </td>
                                                    <td>
                                                        {!! Form::label('field_type', 'Select Type*:', ['class' => 'control-label']) !!}
                                                        {!! Form::select('fields[0][field_type]', array('' => 'Select', 'text' => 'Textbox', 'textarea' => 'Textarea', 'dropdown' => 'Dropdown', 'checkbox' => 'Checkbox Group', 'radio' => 'Radio Group', 'password' => 'Password', 'file' => 'File Upload','email' => 'Email Address'), null, ['class' => 'form-control field_type','id' => 'field_type']) !!}
                                                    </td>
                                                    <td class="items" style="display: none">
                                                        {{Form::label('items', 'Items:')}}
                                                        {{Form::text('fields[0][items]',null,array('class' => 'form-control'))}}
                                                    </td>
                                                    <td class="multiple" style="display: none">
                                                        {{Form::label('multiple', 'Multiple:')}}
                                                        {{ Form::radio('fields[0][multiple]', '1', false)}}
                                                        {{ Form::label('Yes', 'Yes')}}
                                                        {{ Form::radio('fields[0][multiple]', '0', true)}}
                                                        {{ Form::label('No', 'No') }}
                                                    </td>
                                                    <td colspan="2">
                                                        {{Form::label('active', 'Active:')}}
                                                        {{ Form::radio('fields[0][active]', '1', true)}}
                                                        {{ Form::label('Yes', 'Yes')}}
                                                        {{ Form::radio('fields[0][active]', '0', false)}}
                                                        {{ Form::label('No', 'No') }}
                                                    </td>
                                                    <td colspan="3">
                                                        {{Form::label('required', 'Required:')}}
                                                        {{ Form::radio('fields[0][required]', '1', true)}}
                                                        {{ Form::label('Yes', 'Yes')}}
                                                        {{ Form::radio('fields[0][required]', '0', false)}}
                                                        {{ Form::label('No', 'No') }}
                                                    </td>
                                                </tr>
                                                <tr id='addr1'></tr>
                                                @endif
                                        </tbody>
                                    </table>
                                    <div class="col-lg-12">
                                        <div class="pull-right">
                                            <a class="btn btn-default" id="add_row" >Add Row</a>
                                            <a class="btn btn-default" id='delete_row' >Delete Row</a>
                                        </div>
                                    </div>
                                    <!-- <div class="col-lg-12">
                                        <div class="pull-right">
                                            {{Form::submit('Submit',array('class' => 'btn btn-primary'))}}
                                            <a href="{{ route('events.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
                                        </div>
                                    </div>
                                    {{Form::hidden('deletedFieldId',null, array('id' => 'deletedFieldId') )}}
                                    {!! Form::close() !!} -->
                                </div>
                            </div>

                        </div>

                        <div role="tabpanel" class="tab-pane" id="event_images">
                            <div class="col-xs-12 col-sm-12 col-md-12 text-right">
                                <a class="btn btn-primary" id="upl-btn" data-toggle="modal" data-type="DocumentAdd" data-target="#DocumentModal">Upload Images</a>
                            </div>
                            <div class="row" id="uploaded_img_file_list">

                            </div>

                        </div>

                        <div role="tabpanel" class="tab-pane" id="event_videos">
                            <div class="col-xs-12 col-sm-12 col-md-12 text-right">
                                <a class="btn btn-primary" id="upl-btn" data-toggle="modal" data-type="DocumentAdd" data-target="#DocumentModal">Upload Videos</a>
                            </div>
                            <div class="row" id="uploaded_vid_file_list">

                            </div>

                        </div>

                        <div role="tabpanel" class="tab-pane" id="event_documents">
                            <div class="col-xs-12 col-sm-12 col-md-12 text-right">
                                <a class="btn btn-primary" id="upl-btn" data-toggle="modal" data-type="DocumentAdd" data-target="#DocumentModal">Upload Documents</a>
                            </div>
                            <div class="row" id="uploaded_doc_file_list">

                            </div>
                        </div>

                        <div role="tabpanel" class="tab-pane" id="event_tickets">
                            <div class="panel panel-default">
                                <div class="panel-heading">Ticket Management</div>
                                <div class="panel-body">
                                    <div class="col-md-12">
                                        <a class="btn btn-success" data-toggle="modal" data-type="TicketAdd" data-target="#TicketModal">Create New Ticket</a>
                                        <input type="button" class="btn btn-danger" id="deleteMenu" value="Delete Ticket">
                                        {!! Form::open(['method' => 'DELETE','route' => ['eventTicket.ticketDestroy'],'style'=>'display:inline', 'id' => 'deleteTicketForm']) !!}
                                        <input type="hidden" name="TicketIDs" id="ticketvalue" value="">
                                        {!! Form::close() !!}
                                    </div>

                                    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered"
                                           id="ticketTable">
                                        <thead>
                                            <tr>
                                                <th><input name="select_all" type="checkbox"></th>
                                                <th>Title</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($eventtickets as $eventticket)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="select_one" value="{{$eventticket->id}}" class="select_one">
                                                </td>
                                                <td>
                                                    <a style="cursor: pointer;" onclick="updateTicketDetail({{$eventticket->id}})">{{ $eventticket->title }}</a>
                                                </td>
                                                <td align="center">
                                                    @if($eventticket->status == 1)
                                                        {!! Form::open(['route' => ['events.ticketchangeStatus', $eventticket->id],'style'=>'display:inline']) !!}
                                                        <a class="btn btn-micro active" href="javascript:void(0);" onclick="" title=""
                                                           data-original-title="Unpublish Item">
                                                            <span class="glyphicon glyphicon-ok" aria-hidden="false"
                                                                  style="color: green; font-size: 15px;"></span>
                                                            <input type="hidden" name="mode" id="statusmode" value="0">
                                                        </a>
                                                        {!! Form::close() !!}

                                                    @else
                                                        {!! Form::open(['route' => ['events.ticketchangeStatus', $eventticket->id],'style'=>'display:inline']) !!}
                                                        <a class="btn btn-micro active" href="javascript:void(0);" onclick="" title=""
                                                           data-original-title="Publish Item">
                                                            <span class="glyphicon glyphicon-remove" aria-hidden="false"
                                                                  style="color: red; font-size: 15px;"></span>
                                                            <input type="hidden" name="mode" id="statusmode" value="1">
                                                        </a>
                                                        {!! Form::close() !!}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="sectionModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete cover image.</p>
                {!! Form::open(['route' => ['events.deleteCover'],'id' => 'deleteImage']) !!}
                    <input type="hidden" name="eventId" id="eventId" value="{{$item->eventid}}">
                {!! Form::close() !!}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default delete-cover" data-dismiss="modal">Yes</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add modal code for iamge gallery Start here-->
<link href="{{ asset('/dropzone/css/dropzone.css') }}" rel="stylesheet">
<div class="modal fade" id="DocumentModal" tabindex="-1" role="dialog" aria-labelledby="DocumentModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <section class="panel">
                <div class="panel-heading">
                    <span id="head-title">Add Images</span>
                    <span class="tools pull-right">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="file_modal_close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </span>
                </div>
                <div class="modal-body">
                    <div id="file_message" class="alert alert-danger hide"></div>
                    <div class="row">
                        <div class="col-lg-4">
                            <label>Select Upload Type</label>
                        </div>
                        <div class="col-lg-6">
                            <select class="form-control m-bot15" id="upload_type">
                                <option value="drag">Select Drag Drop</option>
                                <option value="drop">Select Drop Box</option>
                                <!-- <option value="drive">Select Google Drive</option> -->
                            </select>
                        </div>
                    </div>
                    <!-- Drag and drop code -->
                    <div id="drag_upload">
                        <form name="DocumentModalForm" id="DocumentModalForm" class="dropzone"  enctype="multipart/form-data"  method="post" action="{{ url('events/'.$item->eventid.'/image-operation') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" id="tabid" name="tabid" value="">
                        </form>

                    </div>

                    <div id="dropbox_upload" style="display:none;">
                        <script src="https://www.dropbox.com/static/api/2/dropins.js" id="dropboxjs" data-app-key="u1bbuyw202a8zjb" type="text/javascript"></script>
                        <div class="row">
                            <div class="col-lg-4">
                                <label>Upload From Dropbox</label>
                            </div>
                            <div class="col-lg-6">
                                <button class="btn btn-warning" id="dropbox_choose_button">Choose File From Dropbox</button>
                            </div>
                        </div>
                        <div class="row" id="dbox-loader" style="display: none;padding-top:10px;" align="center">
                            <img src="{{asset('/images/loader_white.gif')}}" >
                        </div>
                        <script>
                        $('#dropbox_choose_button').click(
                            function () {
                                Dropbox.choose({
                                    linkType: "direct",
                                    multiselect: true,
                                    success: function (files) {
                                        $('#dbox-loader').show();
                                        var CSRF_TOKEN = '{{ csrf_token() }}';
                                        var eventId = {{ $item->eventid }};
                                        var tabid  = jQuery('#tabid').val();
                                        $.ajax({
                                            url : '{{url('/events/'.$item->eventid.'/image-url-operation')}}',
                                            type : 'POST',
                                            data : {
                                                _token : CSRF_TOKEN,
                                                files:files,
                                                tabId:tabid,
                                            },
                                            dataType : 'JSON',
                                            success : function(response) {
                                                if (response.success) {
                                                    $('#dbox-loader').hide();
                                                    return get_upload_file_list();
                                                } else {
                                                    $('#dbox-loader').hide();
                                                    alert(response.message);
                                                }
                                            }
                                        });
                                    }
                                });
                            });
                        </script>
                    </div>

                    <script type="text/javascript" src="https://apis.google.com/js/api.js"></script>
                    <script src="https://www.google.com/jsapi?key=AIzaSyCbOhDpUM5tkeB0OJZY5i0Fn5-T9KiEHVs"></script>
                    <script>
                        function onApiLoad(){
                            gapi.load('auth',{'callback':onAuthApiLoad});
                            gapi.load('picker');
                        }
                        function onAuthApiLoad(){
                            window.gapi.auth.authorize({
                                //'client_id':'537811974579-ee1lhnt1e5qaroudiva4mq6od2cn8cn4.apps.googleusercontent.com',
                                'client_id':'708724978248-d507i53k5dbluv93ff8n4q1bmtiehkdt.apps.googleusercontent.com',
                                'scope':['https://www.googleapis.com/auth/drive']
                            },handleAuthResult);
                        }
                        var oauthToken;
                        function handleAuthResult(authResult){
                            if(authResult && !authResult.error){
                                oauthToken = authResult.access_token;
                                createPicker();
                            }
                        }
                        function createPicker(){
                            var picker = new google.picker.PickerBuilder()
                                .enableFeature(google.picker.Feature.MULTISELECT_ENABLED)
                                .addView(new google.picker.DocsView())
                                .addView(new google.picker.DocsUploadView())
                                .setOAuthToken(oauthToken)
                                //.setDeveloperKey('AIzaSyCcl5n8mBonH1gJZrpcwT5-17ga4HjldGQ')
                                .setDeveloperKey('AIzaSyCbOhDpUM5tkeB0OJZY5i0Fn5-T9KiEHVs')
                                .setCallback(pickerCallback)
                                .build();
                            picker.setVisible(true);
                        }

                        function pickerCallback(data) {
                            if (data[google.picker.Response.ACTION] == google.picker.Action.PICKED) {
                                var doc = data[google.picker.Response.DOCUMENTS];
                                $('#drive-loader').show();
                                    var CSRF_TOKEN = '{{ csrf_token() }}';
                                    var eventId = {{ $item->eventid }};
                                    var tabid  = jQuery('#tabid').val();
                                    $.ajax({
                                        url : '{{url('/events/'.$item->eventid.'/image-url-operation')}}',
                                        type : 'POST',
                                        data : {
                                            _token : CSRF_TOKEN,
                                            files:doc,
                                            uploadtype:'google-drive',
                                            tabId:tabid,
                                        },
                                        dataType : 'JSON',
                                        success : function(response) {
                                            if (response.success) {
                                                $('#drive-loader').hide();
                                                return get_upload_file_list();
                                            } else {
                                                $('#drive-loader').hide();
                                                alert('some Error');
                                            }
                                        }
                                    });
                            }
                        }
                    </script>
                    <div id="googledrive_upload" style="display:none;">
                        <div class="row">
                            <div class="col-lg-4">
                                <label>Upload From Google Drive</label>
                            </div>
                            <div class="col-lg-6">
                                <button class="btn btn-warning" id="drive_choose_button">Choose File From Googledrive</button>
                            </div>
                        </div>
                        <div class="row" id="drive-loader" style="display: none;padding-top:10px;" align="center">
                            <img src="{{asset('/images/loader_white.gif')}}" >
                        </div>
                    </div>
                    <script>
                        $(document).on('click','#drive_choose_button',function(){
                            onApiLoad();
                        });
                    </script>
                    <div class="row">
                        <div class="col-lg-12">
                            <hr/>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
<!-- Add modal code for iamge gallery End here-->

<!-- Add modal code for create and update ticket start here-->
<div class="modal fade" id="TicketModal" tabindex="-1" role="dialog" aria-labelledby="TicketModalLabel">
    <div class="modal-dialog" role="ticket">
        <div class="modal-content">
            <div class="modal-header">
                <span id="head-title">Add Ticket</span>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                {!! Form::open(['route' => ['events.storeTicket'], 'id' => 'StoreTicket', 'name' => 'storeTicket', 'method' => 'post', 'files' => 'true'] ) !!}
                    <input type="hidden" name="event_id" id="event_id" value="{{$item->eventid}}">
                    <input type="hidden" name="id" id="id" value="">
                    <div class="form-group title">
                        {{Form::label('title', 'Title:*')}}
                        {{Form::text('title',null,array('class' => 'form-control'))}}
                    </div>
                    <div class="form-group">
                        {{Form::label('short_desc', 'Short Description:')}}
                        {{Form::textarea('short_desc',null,array('class' => 'form-control', 'rows' => '2', 'cols'=> '25'))}}
                    </div>
                    <div class="form-group">
                        {{Form::label('full_desc', 'Full Description:')}}
                        {{Form::textarea('full_desc',null,array('class' => 'form-control', 'rows' => '4', 'cols'=> '50'))}}
                    </div>
                    <div class="form-group title">
                        {{Form::label('ticket_price', 'Ticket Price:')}}
                        {{Form::text('ticket_price',null,array('class' => 'form-control'))}}
                    </div>
                    <div class="form-group title">
                        {{Form::label('ticket_currency', 'Ticket Currency:')}}
                        {{Form::text('ticket_currency',null,array('class' => 'form-control'))}}
                    </div>

                    <div class="form-group row">
                        {{Form::label('ticket_image', 'Ticket Image:', ['class' => 'col-md-2 col-form-label'])}}
                        <div class="col-md-2">
                            {{Form::file('ticket_image')}}
                        </div>
                    </div>
                    <div class="form-group row ">
                        <img id="ticket_image_src" src="" />
                    </div>
                    <div class="form-group">
                        {{Form::label('status', 'Status:')}}
                        <div class="form-inline">
                            <div class="radio">
                                {{ Form::radio('status', '1', true)}}
                                {{ Form::label('Yes', 'Yes')}}
                            </div>
                            <div class="radio">
                                {{ Form::radio('status', '0', false)}}
                                {{ Form::label('No', 'No') }}
                            </div>
                        </div>
                    </div>
                {!! Form::close() !!}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default store-ticket" data-dismiss="modal">Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Add modal code for create and update ticket start here-->


<script src="{{ asset('/dropzone/dropzone.js') }}" type="text/javascript"></script>
<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#technig').summernote({
        height:300,
    });

    $("#start_date").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd",
        minDate: 0
    });

    $("#end_date").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd",
        minDate: 0
    });

    $('#start_time').timepicker({
        timeFormat: 'HH:mm',
        interval: 30,
        scrollbar: true
    });

    $('#end_time').timepicker({
        timeFormat: 'HH:mm',
        interval: 30,
        scrollbar: true
    });


    var pageType = $('.pagetype').children('input[name=pagetype]:checked').val();
    if(pageType == 'html'){
        jQuery(".page-html").css('display', 'block');
        jQuery(".page-url").css('display', 'none');
    }else if(pageType == 'url'){
        jQuery(".page-html").css('display', 'none');
        jQuery(".page-url").css('display', 'block');
    }


    $('.pagetype').children('input').click(function(event){
        var selectedValue = jQuery(this).val();
        if (selectedValue == 'html'){
            jQuery(".page-url").fadeOut('slow');
            jQuery(".page-html").fadeIn('slow');
        }else if(selectedValue == 'url'){
            jQuery(".page-html").fadeOut('slow');
            jQuery(".page-url").fadeIn('slow');
        }
    });

    var input = document.getElementById('venue_address');
    var autocomplete = new google.maps.places.Autocomplete(input);

    $("#venue_address").change(function(){
        var geocoder = new google.maps.Geocoder();
        var address = document.getElementById("venue_address").value;
        console.log(address);
        geocoder.geocode( { 'address': address}, function(results, status) {
            console.log(results);
            console.log(status);
            if (status == google.maps.GeocoderStatus.OK) {
                var lat  = results[0].geometry.location.lat();
                var long = results[0].geometry.location.lng();

                jQuery('#latitude').val(lat);
                jQuery('#longitude').val(long);
            }
        });
    });

    jQuery('.remove-cover').click(function(){
        jQuery('#sectionModal').modal('show');
    });

    jQuery('.delete-cover').click(function(){
        jQuery('#deleteImage').submit();
    });

    jQuery('.store-ticket').click(function(){
        jQuery('#StoreTicket').submit();
    });
});

// custom fields code start here
jQuery(document).ready(function(){
    var i  = {{count($eventfields)}};
    var fieldIds = new Array();

    jQuery("#add_row").click(function(){
        jQuery('#addr'+i).html("<td>"+ (i+1) +"</td><td><label for='display_name'>Caption*:</label><input class='form-control' name='fields["+i+"][display_name]' type='text'/></td><td><label for='field_type' class='control-label'>Select Type*:</label><select class='form-control field_type' id='field_type"+i+"' name='fields["+i+"][field_type]'><option value='--' selected='selected'>Select</option><option value='text'>Textbox</option><option value='textarea'>Textarea</option><option value='dropdown'>Dropdown</option><option value='checkbox'>Checkbox Group</option><option value='radio'>Radio Group</option><option value='password'>Password</option><option value='file'>File Upload</option><option value='email'>Email Address</option></select></td><td class='items' style='display: none'><label for='items'>Items:</label><input class='form-control' name='fields["+i+"][items]' id='items' type='text'></td><td class='multiple' style='display: none'><label for='multiple'>Multiple:</label><input name='fields["+i+"][multiple]' value='1' id='multiple' type='radio'><label for='Yes'>Yes</label><input checked='checked' name='fields["+i+"][multiple]'' value='0' id='multiple' type='radio'><label for='No'>No</label></td><td colspan='2'><label for='active'>Active:</label><input checked='checked' name='fields["+i+"][active]' value='1' id='active' type='radio'><label for='Yes'>Yes</label><input name='fields["+i+"][active]'' value='0' id='active' type='radio'><label for='No'>No</label></td><td colspan='3'><label for='required'>Required:</label><input checked='checked' name='fields["+i+"][required]' value='1' id='required' type='radio'><label for='Yes'>Yes</label><input name='fields["+i+"][required]' value='0' id='required' type='radio'><label for='No'>No</label></td>");

        jQuery('#tab_logic').append('<tr id="addr'+(i+1)+'"></tr>');
        i++;
    });
    jQuery("#delete_row").click(function(){

        if(i>1){

            var total = {{count($eventfields)}};
            if(total > 1)
            {
                var cnt = i - 1;

                var fieldId = document.getElementById("fields["+cnt+"][id]").value;
                fieldIds.push(fieldId);
                var fieldIdsStr = fieldIds.join(", ");
                jQuery("#deletedFieldId").val(fieldIdsStr);
            }
            else
            {
                var cnt = i;
            }



            jQuery("#addr"+(i-1)).html('');
            i--;
        }
    });
});

jQuery(document).ready(function() {

    var valueArray = ["dropdown", "checkbox", "radio"];
    var fieldType  = jQuery('.field_type').val();
    if(valueArray.indexOf(fieldType) !== -1){
        jQuery('.items').css('display', 'table-cell');
        jQuery('.itemsHeader').css('display', 'table-cell');
    }

    if(fieldType == 'dropdown'){
        jQuery('.multiple').css('display', 'table-cell');
        jQuery('.multipleHeader').css('display', 'table-cell');
    }

    jQuery(document).on('change', '.field_type', function() {
        // Does some stuff and logs the event to the console
        var selectedValue = jQuery(this).val();
        var itemCss       = jQuery(this).parent('td').next('.items').css('display');
        var multiCss      = jQuery(this).parent('td').siblings('.multiple').css('display');

        if(valueArray.indexOf(selectedValue) !== -1){
            if(itemCss == 'none'){
                jQuery(this).parent('td').next('.items').css('display', 'table-cell');

                if(selectedValue == 'dropdown'){
                    jQuery(this).parent('td').siblings('.multiple').css('display', 'table-cell');
                }else{
                    jQuery(this).parent('td').siblings('.multiple').css('display', 'none');
                }
            }
            }else{
                if(itemCss == 'table-cell'){
                    jQuery(this).parent('td').next('.items').css('display', 'none');
                }

                if(multiCss == 'table-cell'){
                    jQuery(this).parent('td').siblings('.multiple').css('display', 'none');
                }
            }
    });
});

// image gallery code start here...
jQuery(document).ready(function($) {
    jQuery(document).on('click','.btnNext',function() {
        jQuery('#tab a[href="#event_fields"]').tab('show');
    });
    jQuery('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
        var activated_tab = jQuery(e.target).attr('href');
        jQuery("#tabid").val(activated_tab.split("#")[1]);
        if ( activated_tab == '#event_images'){
            jQuery("#head-title").html("Add Images");
            jQuery('#uploaded_file_list').html('');
            get_upload_file_list();
        } else if (activated_tab == "#event_videos") {
            $("#head-title").html("Add Videos");
            $('#uploaded_file_list').html('');
            get_upload_file_list();
        } else if (activated_tab == "#event_documents") {
            $("#head-title").html("Add Documents");
            $('#uploaded_file_list').html('');
            get_upload_file_list();
        }
    });
});

Dropzone.options.DocumentModalForm = {
    paramName : "uploaded_files", // The name that will be used to transfer the file
    //maxFilesize : 2, // MB
    method : "post",
    icontext: "Drop files here or click to upload.",
    init : function() {
        this.on("complete", function(file) {
            //$('.modal-backdrop').css('height',$(document).height()+($("#uploaded_file_list").height()+$("#DocumentModalForm").height())/2+'px');
            return get_upload_file_list();
            this.removeFile(file);
        });
    },
    error: function(file, response) {
       alert(response.uploaded_files);
    },
};
// Set upload form accrding to the selected type
$(document).on('click','a[data-type=DocumentAdd]',function() {
        //Set upload type
        set_upload_form($('#upload_type').val());
        $('#upload_type').change(function(){
            set_upload_form($(this).val());
        });
        //get_upload_file_list();
});

function set_upload_form(type)
{
    if(type == 'drag')
    {
        $('#drag_upload').show();
        $('#dropbox_upload').hide();
        $('#googledrive_upload').hide();
        $('#drag_drop_button').show();
    }
    else if(type == 'drop')
    {
        $('#drag_upload').hide();
        $('#dropbox_upload').show();
        $('#googledrive_upload').hide();
        $('#drag_drop_button').show();
    }
    else if(type == 'drive')
    {
        $('#drag_upload').hide();
        $('#dropbox_upload').hide();
        $('#googledrive_upload').show();
        $('#drag_drop_button').show();
    }

}

function get_upload_file_list()
{
    var CSRF_TOKEN = '{{ csrf_token() }}';
    var tabid  = jQuery('#tabid').val();

    $.ajax({
        url : "{{url('/events/getuploadfilelist')}}",
        type : 'POST',
        name : 'getuploadfilelist',
        id : 'getuploadfilelist',
        async: false,
        data : {
            _token : CSRF_TOKEN,
            eventid : {{$item->eventid}},
            tabId : tabid,
        },
        dataType : 'JSON',
        success : function(response) {
            if (response.success)
            {
                if(tabid == 'event_images')
                {
                    $('#uploaded_img_file_list').html(response.data);
                }
                if(tabid == 'event_videos')
                {
                    $('#uploaded_vid_file_list').html(response.data);
                }
                if(tabid == 'event_documents')
                {
                    $('#uploaded_doc_file_list').html(response.data);
                }

                $( "div" ).remove( ".dz-complete" );
                $('.dz-message').css('display', 'block');
                $('.modal-backdrop').css('display', 'none');
                $('#DocumentModal').modal('hide');
            }
            else
            {
                //$('#uploaded_file_list').html(response.data);
                if(tabid == 'event_images')
                {
                    $('#uploaded_img_file_list').html(response.data);
                }
                if(tabid == 'event_videos')
                {
                    $('#uploaded_vid_file_list').html(response.data);
                }
                if(tabid == 'event_documents')
                {
                    $('#uploaded_doc_file_list').html(response.data);
                }
                $( "div" ).remove( ".dz-complete" );
                $('.dz-message').css('display', 'block');
                $('.modal-backdrop').css('display', 'none');
                $('#DocumentModal').modal('hide');
            }
        }
    });
}

$(document).ready(function(){
        $(document).on('click','.removeImage',function(){
        $('#drive-loader').show();
        var CSRF_TOKEN = '{{ csrf_token() }}';
        var imageId = $(this).attr('imageId');
        $.ajax({
            url : '{{url('/events/'.$item->eventid.'/image-remove')}}',
            type : 'POST',
            data : {
                _token : CSRF_TOKEN,
                imageId:imageId,
            },
            dataType : 'JSON',
            success : function(response) {
                if (response.success) {
                    $('#drive-loader').hide();
                    return get_upload_file_list();
                } else {
                    $('#drive-loader').hide();
                    alert('some Error');
                }
            }
        });
    });

    $(document).on('click','.removeVideo',function(){
        $('#drive-loader').show();
        var CSRF_TOKEN = '{{ csrf_token() }}';
        var videoId = $(this).attr('videoId');
        $.ajax({
            url : '{{url('/events/'.$item->eventid.'/video-remove')}}',
            type : 'POST',
            data : {
                _token : CSRF_TOKEN,
                videoId:videoId,
            },
            dataType : 'JSON',
            success : function(response) {
                if (response.success) {
                    $('#drive-loader').hide();
                    return get_upload_file_list();
                } else {
                    $('#drive-loader').hide();
                    alert('some Error');
                }
            }
        });
    });

    $(document).on('click','.removeDocument',function(){
        $('#drive-loader').show();
        var CSRF_TOKEN = '{{ csrf_token() }}';
        var docId = $(this).attr('docId');
        $.ajax({
            url : '{{url('/events/'.$item->eventid.'/doc-remove')}}',
            type : 'POST',
            data : {
                _token : CSRF_TOKEN,
                docId:docId,
            },
            dataType : 'JSON',
            success : function(response) {
                if (response.success) {
                    $('#drive-loader').hide();
                    return get_upload_file_list();
                } else {
                    $('#drive-loader').hide();
                    alert('some Error');
                }
            }
        });
    });
});

jQuery(document).ready(function ($) {
    var CmsPageIDs = [];
    $('#ticketTable').DataTable({
        select: true,
        "aoColumnDefs": [
            {"bSortable": false, "aTargets": [0]},
            ],
    });
    jQuery("th:first").removeClass('sorting_asc');

    jQuery('td input[name="select_one"]').on('click', function (e) {
        if (this.checked) {
            var pageID = jQuery(this).val();
            CmsPageIDs.push(pageID);
            jQuery('#ticketvalue').val(CmsPageIDs);
        } else {
            var pageID = jQuery(this).val();
            var removeIndex = jQuery.inArray(pageID, CmsPageIDs);
            CmsPageIDs.splice(removeIndex, 1);
            jQuery('#ticketvalue').val(CmsPageIDs);
        }
        // Prevent click event from propagating to parent
        e.stopPropagation();
    });

    // Handle click on "Select all" control
    jQuery('th input[name="select_all"]').on('click', function (e) {
        if (this.checked) {
            jQuery('#ticketTable tbody input[type="checkbox"]:not(:checked)').trigger('click');
            var allPageIDs = [];
            jQuery('.select_one').each(function (index, el) {
                allPageIDs.push(jQuery(this).val());
            });
            jQuery('#ticketvalue').val(allPageIDs);
        } else {
            jQuery('#ticketTable tbody input[type="checkbox"]:checked').trigger('click');
            jQuery('#ticketvalue').val("");
        }
        // Prevent click event from propagating to parent
        e.stopPropagation();
    });

    jQuery('#deleteMenu').click(function (event) {
        var deleteId = jQuery('#ticketvalue').val();
        if (deleteId != '') {
            jQuery('#deleteTicketForm').submit();
        } else {
            alert('Please select atleast one Item !');
        }
    });

    jQuery('.btn-micro').click(function (event) {
        var form = jQuery(this).parent('form');
        jQuery(form).submit();
    });

    jQuery('#TicketModal').on('hidden.bs.modal', function (e) {
        $("#id").val('');
        $("#event_id").val('');
        $("#title").val('');
        $("#short_desc").val('');
        $("#full_desc").val('');
        $("#ticket_price").val('');
        $("#ticket_currency").val('');
        $('#ticket_image_src').attr('src','' );
        $("#status").val(1);
    });

});

function updateTicketDetail(ticketId)
{
    var CSRF_TOKEN = '{{ csrf_token() }}';
    $.ajax({
        url : "{{url('/events/getTicketRecords')}}",
        type : 'POST',
        name : 'getTicketRecords',
        id   : 'getTicketRecords',
        async: false,
        data : {
            _token : CSRF_TOKEN,
            ticketId : ticketId,
        },
        dataType : 'JSON',
        success : function(response)
        {
            if (response.success)
            {
                $('#TicketModal').modal('show');
                $("#id").val(response.data.id);
                $("#event_id").val(response.data.event_id);
                $("#title").val(response.data.title);
                $("#short_desc").val(response.data.short_desc);
                $("#full_desc").val(response.data.full_desc);
                $("#ticket_price").val(response.data.ticket_price);
                $("#ticket_currency").val(response.data.ticket_currency);
                var src = response.data.ticket_image;
                $('#ticket_image_src').attr('src',src );
                $("#status").val(response.data.status);
            }
        }
    });
}





// image gallery code end here...
</script>
@endsection
