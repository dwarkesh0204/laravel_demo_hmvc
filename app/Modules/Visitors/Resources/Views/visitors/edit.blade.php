@extends('visitors::layouts.app')
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
                <strong>Edit Subject</strong>
            </div>
            <div class="panel-body">
                {!! Form::model($data, ['method' => 'PATCH','route' => ['visitors.update', $data->id]]) !!}
                <div class="box-body">
                    <div class="form-group">
                        {!! Form::label('name','Visitor Name:*') !!}<br />
                        {!! Form::text('name', null, array('placeholder' => 'Visitor Name','class' => 'form-control')) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('phone_no','Contact Number:') !!}<br />
                        {!! Form::text('phone_no', null, array('placeholder' => 'Contact Number','class' => 'form-control')) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('email','Email:') !!}<br />
                        {!! Form::text('email', null, array('placeholder' => 'Email Address','class' => 'form-control')) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('purpose','Purpose:') !!}<br />
                        {!! Form::textarea('purpose', null, ['placeholder' => 'Purpose','class' => 'form-control']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('card_no','Card No:*') !!}<br />
                        {!! Form::select('card_no',(['' => 'Select Card'] + $lists),NULL,['name'=>'card_no','id'=>'card_no','class' => 'form-control']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('mac_add','Mac Address:') !!}<br />
                        {!! Form::text('mac_add', null, array('placeholder' => 'Mac Address','class' => 'form-control', 'readonly' => 'true')) !!}
                    </div>
                    <div class="form-group">
                        <div class="pull-right">
                            <input type="submit" class="btn btn-primary" value="Submit">
                            <a href="{{ route('visitors.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
                        </div>
                    </div>
               </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection