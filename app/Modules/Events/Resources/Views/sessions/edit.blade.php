@extends('events::layouts.app')
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
                <strong>Edit Session</strong>
                <span class="back pull-right"><a href="{{ route('sessions.index') }}">Back</a></span>
            </div>
            <div class="panel-body">
                {!! Form::model($session, ['method' => 'PATCH','route' => ['sessions.update', $session->id]]) !!}
                    <div class="box-body">
                        <div class="form-group title">
                            {{Form::label('name', 'Title:*')}}
                            {{Form::text('name',null,array('class' => 'form-control', 'placeholder'=>'Title'))}}
                        </div>
                        <div class="form-group">
                            {!! Form::label('event_id','Event:*') !!}<br />
                            {!! Form::select('event_id',(['0' => 'Select a Event'] + $events),$session->event_id,['class' => 'form-control']) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('speaker_id','Speaker') !!}<br />
                            {!! Form::select('speaker_id',(['0' => 'Select a Speaker'] + $speakers),$session->session_sessions_speakers->lists('sessions_speakers_users')->lists('id')->toArray(),['name'=>'speaker_id[]','multiple'=>'multiple','class' => 'form-control']) !!}
                        </div>
                        <div class="form-group">
                            {{Form::label('description', 'Description:')}}
                            {{Form::textarea('description',null,array('class' => 'form-control','id' => 'technig'))}}
                        </div>
                        <div class="form-group title">
                            {{Form::label('capacity', 'Capacity:*')}}
                            {{Form::text('capacity',null,array('class' => 'form-control', 'placeholder'=>'Capacity'))}}
                        </div>
                        <div class="form-group title">
                            {{Form::label('venue', 'Venue:*')}}
                            {{Form::text('venue',null,array('class' => 'form-control', 'placeholder'=>'Venue'))}}
                        </div>
                        <div class="form-group row">
                            {{Form::label('type', 'Type*:', ['class' => 'col-md-2 col-form-label'])}}
                            <div class="form-inline col-md-3">
                                <div class="radio">
                                    {{ Form::radio('type', 'inviteonly', false)}}
                                    {{ Form::label('Inviteonly', 'Inviteonly')}}
                                </div>
                                <div class="radio">
                                    {{ Form::radio('type', 'open', true)}}
                                    {{ Form::label('Open', 'Open') }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group row>
                            <div class="col-md-12">
                                    <div class="form-group col-md-4">
                                        {{Form::label('date', 'Date:')}}
                                        {{Form::text('date', null, ['class' => 'form-control'])}}
                                    </div>
                                    <div class="form-group col-md-4 ">
                                        {{Form::label('start_time', 'Start Time:')}}
                                        {{Form::text('start_time', null, ['class' => 'form-control'])}}
                                    </div>
                                    <div class="form-group col-md-4">
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
                            {{Form::submit('Save',array('class' => 'btn btn-primary btn-sm'))}}
                        </div>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#technig').summernote({
            height:300,
        });

        $("#date").datepicker({
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
    });
</script>
@endsection
