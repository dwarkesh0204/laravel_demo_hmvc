@extends('events::layouts.app')
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
                <strong>User</strong>
                <span class="back pull-right"></span>
            </div>
            <div class="panel-body">

        {!! Form::open(array('route' => 'users.store','method'=>'POST')) !!}

        <div class="row">

            <div class="col-xs-12 col-sm-12 col-md-12">

                <div class="form-group">

                    <strong>Name:</strong>

                    {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}

                </div>

            </div>

            <div class="col-xs-12 col-sm-12 col-md-12">

                <div class="form-group">

                    <strong>Email:</strong>

                    {!! Form::text('email', null, array('placeholder' => 'Email','class' => 'form-control')) !!}

                </div>

            </div>

            <div class="col-xs-12 col-sm-12 col-md-12">

                <div class="form-group">

                    <strong>Contact No:</strong>

                    {!! Form::text('phone_number', null, array('placeholder' => 'Contact No','class' => 'form-control')) !!}

                </div>

            </div>

            <div class="col-xs-12 col-sm-12 col-md-12">

                <div class="form-group">

                    <strong>Password:</strong>

                    {!! Form::password('password', array('placeholder' => 'Password','class' => 'form-control')) !!}

                </div>

            </div>

            <div class="col-xs-12 col-sm-12 col-md-12">

                <div class="form-group">

                    <strong>Confirm Password:</strong>

                    {!! Form::password('confirm-password', array('placeholder' => 'Confirm Password','class' => 'form-control')) !!}

                </div>

            </div>

            <div class="col-xs-12 col-sm-12 col-md-12">

                <div class="form-group">

                    <strong>Role:</strong>

                    {!! Form::select('roles[]', $roles,[], array('class' => 'form-control','multiple')) !!}

                </div>

            </div>

            <div class="col-lg-12">
                <div class="pull-right">
                <input type="submit" class="btn btn-primary" value="Submit">
                <a href="{{ route('users.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
                </div>
            </div>

        </div>

        {!! Form::close() !!}
                </div></div>
    </div>
@endsection