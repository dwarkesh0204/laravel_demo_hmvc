@extends('attendance::layouts.app')
@section('content')
    <div class="container">
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
                <strong>Teacher</strong>
                <span class="back pull-right"></span>
            </div>
            <div class="panel-body">
                {!! Form::open(array('route' => 'teachers.store','method'=>'POST', 'files' => 'true')) !!}
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
                        <div class="col-md-2">
                            {!! Form::file('avatar') !!}
                        </div>
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
                    <div class="col-lg-12">
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
@endsection