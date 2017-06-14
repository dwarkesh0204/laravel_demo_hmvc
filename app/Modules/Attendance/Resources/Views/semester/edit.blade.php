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
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>Edit Semester</strong>
            </div>
            <div class="panel-body">
                {!! Form::model($data, ['method' => 'PATCH','route' => ['semester.update', $data->id]]) !!}
                <div class="box-body">
                    <div class="form-group">
                        {!! Form::label('courseid','Course:*') !!}<br />
                        {!! Form::select('courseid',(['' => 'Select Course'] + $courses),NULL,['name'=>'courseid','id'=>'courseid','class' => 'form-control']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('name','Name:*') !!}<br />
                        {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('description','Description:*') !!}<br />
                        {!! Form::textarea('description', null, ['placeholder' => 'Description','class' => 'form-control']) !!}
                    </div>
                    <div class="form-group">
                        <div class="pull-right">
                            <input type="submit" class="btn btn-primary" value="Submit">
                            <a href="{{ route('semester.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
                        </div>
                    </div>
               </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection