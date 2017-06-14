@extends('attendance::layouts.app')
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
            @if ($message = Session::get('error'))
                <div class="alert alert-danger">
                    <p>{{ $message }}</p>
                </div>
            @endif
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>Edit Teacher</strong>
            </div>
            <div class="panel-body">
                {!! Form::model($data, ['method' => 'PATCH','route' => ['teachers.update', $data->id], 'files' => 'true']) !!}
                <div class="box-body">
                    <div class="form-group">
                        {!! Form::label('name','Name:*') !!}<br />
                        {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('email','Eamil:*') !!}<br />
                        {!! Form::text('email', null, ['placeholder' => 'Email Address','class' => 'form-control']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('phone_number','Contact No:*') !!}<br />
                        {!! Form::text('phone_number', null, array('placeholder' => 'Contact No','class' => 'form-control')) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('password','Password:*') !!}<br />
                        {!! Form::password('password', null, ['placeholder' => 'Password','class' => 'form-control']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('confirm-password','Confirm Password:*') !!}<br />
                        {!! Form::password('confirm-password', null, ['placeholder' => 'Confirm Password','class' => 'form-control']) !!}
                    </div>
                    <div class="form-group row">
                        {!! Form::label('avatar', 'Avatar:', ['class' => 'col-md-2 col-form-label']) !!}
                        <div class="col-md-3">
                            {!! Form::file('avatar') !!}
                        </div>
                            @if(!empty($data->avatar))
                                <div class="col-md-4">
                                    {{Html::image(asset($data->avatar), $data->avatar, ['class' => 'cover-image', 'height' => '150px', 'width'=> '200px'])}}
                                    <span class="glyphicon glyphicon-remove remove-cover" aria-hidden="false" style="color: red; cursor: pointer; margin-left: -20px; position: absolute; font-size: 20px;"></span>
                                </div>
                            @endif
                    </div>

                    <div class="form-group row">
                            {{Form::label('gender', 'Gender:', ['class' => 'col-md-2 col-form-label'])}}
                        <div class="form-inline col-md-3">
                            <div class="radio">
                                {{ Form::radio('gender', 'male', false)}}
                                {{ Form::label('Male', 'Male')}}
                            </div>
                            <div class="radio">
                                {{ Form::radio('gender', 'female', false)}}
                                {{ Form::label('Female', 'Female') }}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('qualification','Qualification:*') !!}<br />
                        {!! Form::text('qualification', null, ['placeholder' => 'Qualification','class' => 'form-control']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('description','Description:*') !!}<br />
                        {!! Form::textarea('description', null, ['placeholder' => 'Description','class' => 'form-control']) !!}
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
                    <div class="form-group">
                        <div class="pull-right">
                            <input type="submit" class="btn btn-primary" value="Submit">
                            <a href="{{ route('teachers.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
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
                {!! Form::open(['route' => ['teachers.deleteImage'],'id' => 'deleteImage']) !!}
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