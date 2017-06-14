@extends('layouts.app')
@section('content')
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
            <div class="panel-heading">
                <strong>Ads Manager (Edit)</strong>
            </div>
            <div class="panel-body">
                {!! Form::model($item, ['method' => 'PATCH','files' => 'true', 'route' => ['adsManager.update', $item->id]]) !!}
                    <div class="box-body">
                        <div class="form-group title">
                            {{Form::label('title', 'Title:*')}}
                            {{Form::text('title',null,array('class' => 'form-control', 'placeholder'=>'Title'))}}
                        </div>

                        <div class="form-group">
                            {{Form::label('description', 'Description:')}}
                            {{Form::textarea('description',null,array('class' => 'form-control', 'placeholder'=>'Description', 'rows' => '4', 'cols'=> '50'))}}
                        </div>

                        <div class="form-group">
                            {{ Form::label('beacon_id','Beacon Location:*') }}<br />
                            {{ Form::select('beacon_id',(['' => 'Select a Beacon Location'] + $beacons),NULL,['class' => 'form-control']) }}
                        </div>

                        <!-- <div class="form-group">
                            {!! Form::label('campaign_manager_id','Camapign Manager:*') !!}<br />
                            {!! Form::select('campaign_manager_id',(['' => 'Select a Camapign Manager'] + $camapignManagers),NULL,['class' => 'form-control']) !!}
                        </div> -->

                        <div class="form-group row page-url">
                            {{Form::label('link', 'Link:', ['class' => 'col-md-2 col-form-label'])}}
                            <div class="col-md-5">
                                {{Form::url('link',null,array('class' => 'form-control'))}}
                            </div>
                        </div>

                        <!-- <div class="form-group row">
                            {{Form::label('image', 'Image:', ['class' => 'col-md-2 col-form-label'])}}
                            <div class="col-md-3">
                                {{Form::file('image')}}
                            </div>
                            @if(!empty($item->image))
                                <div class="col-md-4">
                                    {{Html::image(asset($item->image), $item->image, ['class' => 'cover-image', 'height' => '150px', 'width'=> '200px'])}}
                                    <span class="glyphicon glyphicon-remove remove-image" aria-hidden="false" style="color: red; cursor: pointer; margin-left: -20px; position: absolute; font-size: 20px;"></span>
                                </div>
                            @endif
                        </div> -->

                        <div class="form-group row">
                            <div class="col-md-12">

                                <div class="form-group col-md-3">
                                    {{Form::label('start_date', 'Start Date:')}}
                                    {{Form::text('start_date', null, ['class' => 'form-control'])}}
                                </div>

                                <div class="form-group col-md-3">
                                    {{Form::label('end_date', 'End Date:')}}
                                    {{Form::text('end_date', null, ['class' => 'form-control'])}}
                                </div>

                                <div class="form-group col-md-3">
                                    {{Form::label('start_time', 'Start Time:')}}
                                    {{Form::text('start_time', null, ['class' => 'form-control'])}}
                                </div>

                                <div class="form-group col-md-3">
                                    {{Form::label('end_time', 'End Time:')}}
                                    {{Form::text('end_time', null, ['class' => 'form-control'])}}
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            {{Form::label('status', 'Active:')}}
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

                        <div class="form-group">
                            <div class="pull-right">
                                {{Form::submit('Submit',array('class' => 'btn btn-primary'))}}
                                <a href="{{ route('adsManager.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
                            </div>
                        </div>
                    </div>
                {!! Form::close() !!}
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
                <p>Are you sure you want to delete image.</p>
                {!! Form::open(['route' => ['adsManager.deleteImage'],'id' => 'deleteImage']) !!}
                    <input type="hidden" name="id" id="id" value="{{$item->id}}">
                {!! Form::close() !!}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default delete-image" data-dismiss="modal">Yes</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    jQuery('.remove-image').click(function(){
        jQuery('#sectionModal').modal('show');
    });

    jQuery('.delete-image').click(function(){
        jQuery('#deleteImage').submit();
    });
});
</script>
@endsection