@extends('storesolution::layouts.app')
@section('content')
<div class="container">
    <div class="row">
        @if (count($errors) > 0)
            <div class="alert alert-danger">
                <strong>Whoops!</strong> There were some problems with your input.<br><br>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>Edit Store</strong>
            </div>
            <div class="panel-body">
                {!! Form::model($data, ['method' => 'PATCH','route' => ['stores.update', $data->id],'files' => 'true']) !!}
                <div class="box-body">
                    <div class="form-group">
                        {!! Form::label('name','Store Name:*') !!}<br />
                        {!! Form::text('name', null, array('placeholder' => 'Store Name','class' => 'form-control')) !!}
                    </div>
                     <div class="form-group">
                        {!! Form::label('description','Description:') !!}<br />
                        {!! Form::textarea('description', null, ['placeholder' => 'Description','class' => 'form-control']) !!}
                    </div>
                    <div class="form-group row">
                        {{Form::label('cover_image', 'Image:', ['class' => 'col-md-2 col-form-label'])}}
                        <div class="col-md-3">
                            {{Form::file('cover_image')}}
                        </div>
                        @if(!empty($data->cover_image))
                            <div class="col-md-4">
                                {{Html::image(asset($data->cover_image), $data->image, ['class' => 'cover-image', 'height' => '150px', 'width'=> '200px'])}}
                                <span class="glyphicon glyphicon-remove remove-image" aria-hidden="false" style="color: red; cursor: pointer; margin-left: -20px; position: absolute; font-size: 20px;"></span>
                            </div>
                        @endif
                    </div>
                    <div class="form-group row">
                        {{Form::label('venue', 'Venue Address:', ['class' => 'col-md-2 col-form-label'])}}
                        <div class="col-md-5">
                            {{Form::text('venue', null, ['class' => 'form-control', 'id' => 'venue_address', 'placeholder' => 'Enter Venue address'])}}
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('email','Email:') !!}<br />
                        {!! Form::text('email', null, array('placeholder' => 'Email Address','class' => 'form-control')) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('phone_no','Contact Number:') !!}<br />
                        {!! Form::text('phone_no', null, array('placeholder' => 'Contact Number','class' => 'form-control')) !!}
                    </div>
                    <div class="form-group section-floorplan">
                        {!! Form::label('sections', 'Sections:', ['class' => 'control-label']) !!}
                        {!! Form::text('sections', null, ['class' => 'form-control section-control']) !!}
                        <i class="glyphicon glyphicon-plus-sign pull-right"></i>
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
                            <input type="submit" class="btn btn-primary" value="Submit">
                            <a href="{{ route('stores.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
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
                {!! Form::open(['route' => ['stores.deleteImage'],'id' => 'deleteImage']) !!}
                    <input type="hidden" name="id" id="id" value="{{$data->id}}">
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