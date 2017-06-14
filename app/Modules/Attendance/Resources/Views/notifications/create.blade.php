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
                <strong>Notification</strong>
                <span class="back pull-right"></span>
            </div>
            <div class="panel-body">
                {!! Form::open(array('route' => 'notifications.store','method'=>'POST')) !!}
                <div class="box-body">
                    <div class="form-group">
                        {!! Form::label('subject','Subject:*') !!}<br />
                        {!! Form::text('subject', null, array('placeholder' => 'Subject','class' => 'form-control')) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('notification','Notification:*') !!}<br />
                        {!! Form::textarea('notification', null, ['placeholder' => 'Notification','class' => 'field']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('students','Student:*') !!}<br />
                        {!! Form::select('students',(['0' => 'Select Students'] + $students),NULL,['multiple'=>'multiple','name'=>'students[]','class' => 'form-control']) !!}
                    </div>
                    <div class="col-lg-12">
                        <div class="pull-right">
                            <input type="submit" class="btn btn-primary" value="Submit">
                            <a href="{{ route('notifications.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@endsection