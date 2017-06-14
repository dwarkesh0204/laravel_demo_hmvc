@extends('proximity::layouts.app')

@section('javascript')

@endsection
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
                <strong>Ads Manager (New)</strong>
            </div>
            <div class="panel-body">
                {!! Form::open(array('route' => 'adsManager.store', 'method'=>'POST', 'files' => 'true')) !!}
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

                        <div class="form-group">
                            {{Form::label('link', 'Link:', ['class' => 'col-md-2 col-form-label'])}}
                            {{Form::url('link',null,array('class' => 'form-control'))}}
                        </div>

                        <!-- <div class="form-group row">
                            {{Form::label('image', 'Image:', ['class' => 'col-md-2 col-form-label'])}}
                            <div class="col-md-2">
                                {{Form::file('image')}}
                            </div>
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
<script type="text/javascript">
    jQuery(document).ready(function($) {

        $("#start_date").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: "yy-mm-dd",
            minDate: 0
        });

        $("#end_date").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: "yy-mm-dd",
            minDate: 0
        });

        $('#start_time').timepicker({
            timeFormat: 'HH:mm',
            interval: 60,
            scrollbar: true
        });

        $('#end_time').timepicker({
            timeFormat: 'HH:mm',
            interval: 60,
            scrollbar: true
        });
    });
</script>
@endsection
