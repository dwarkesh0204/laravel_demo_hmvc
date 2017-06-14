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
                <strong>Class</strong>
                <span class="back pull-right"></span>
            </div>
            <div class="panel-body">
                {!! Form::open(array('route' => 'class.store','method'=>'POST')) !!}
                <div class="box-body">
                    <div class="form-group">
                        {!! Form::label('name','Name:*') !!}<br />
                        {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('device_id','Device ID:*') !!}<br />
                        {!! Form::select('device_id',(['' => 'Select Device'] + $lists),NULL,['name'=>'device_id','id'=>'device_id','class' => 'form-control']) !!}
                    </div>
                    <div class="col-lg-12">
                        <div class="pull-right">
                            <input type="submit" class="btn btn-primary" value="Submit">
                            <a href="{{ route('class.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@endsection