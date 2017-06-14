@extends('layouts.app')
@section('javascript')
<script type="text/javascript">
</script>
@endsection
@section('content')
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCa2HEyzmSepTQnzX9deSUhBj_G9x9cSpc&libraries=geometry,places&location=no"></script>
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
                <strong>Manufacturer</strong>
            </div>
            <div class="panel-body">
                {!! Form::open(array('route' => 'iot.store','method'=>'POST','files'=>'true')) !!}
                    {{ Form::hidden('user_id', $user_id) }}
                    <div class="box-body">
                        <div class="form-group title">
                            {{Form::label('title', 'Title:*')}}
                            {{Form::text('title',null,array('class' => 'form-control', 'placeholder'=>'Title'))}}
                        </div>
                        <div class="form-group title">
                            {{Form::label('type', 'Type:*')}}
                            {!! Form::select('type', ['BLE Sniffer'=>'BLE Sniffer','Wifi Sniffer'=>'Wifi Sniffer','BLE and Wifi Sniffer'=>'BLE and Wifi Sniffer','BLE/Wifi Sniffer and BLE Broadcast'=>'BLE/Wifi Sniffer and BLE Broadcast'], null, ['class' => 'form-control','id' => 'type']) !!}
                        </div>
                        <div class="form-group title">
                            {{Form::label('device_id', 'Device Id:*')}}
                            {{Form::text('device_id',null,array('class' => 'form-control', 'placeholder'=>'Device Id'))}}
                        </div>

                        <div class="form-group title">
                            {{Form::label('gateway_ssid', 'Gateway SSID:*')}}
                            {{Form::text('gateway_ssid',null,array('class' => 'form-control', 'placeholder'=>'Gateway ssId'))}}
                        </div>

                        <div class="form-group title">
                            {{Form::label('gateway_password', 'Gateway Password:*')}}
                            {{Form::text('gateway_password',null,array('class' => 'form-control', 'placeholder'=>'Gateway Password'))}}
                        </div>

                        <div class="form-group title">
                            {{Form::label('wifi_name', 'Wifi Name:*')}}
                            {{Form::text('wifi_name',null,array('class' => 'form-control', 'placeholder'=>'Wifi Name'))}}
                        </div>

                        <div class="form-group title">
                            {{Form::label('wifi_password', 'Wifi Password:*')}}
                            {{Form::text('wifi_password',null,array('class' => 'form-control', 'placeholder'=>'Wifi Password'))}}
                        </div>

                        <div class="form-group title">
                            {{Form::label('mac_id', 'Mac Id:*')}}
                            {{Form::text('mac_id',null,array('class' => 'form-control', 'placeholder'=>'Mac Id'))}}
                        </div>

                        <div class="form-group title">
                            {{Form::label('major_id', 'Major Id:*')}}
                            {{Form::text('major_id',null,array('class' => 'form-control', 'placeholder'=>'Major Id'))}}
                        </div>

                        <div class="form-group title">
                            {{Form::label('minor_id', 'Minor Id:*')}}
                            {{Form::text('minor_id',null,array('class' => 'form-control', 'placeholder'=>'Minor Id'))}}
                        </div>

                        <div class="form-group title">
                            {{Form::label('location', 'Location:')}}
                            {{Form::text('location', null, array('class' => 'form-control', 'id' => 'location', 'placeholder' => 'Enter Venue address'))}}
                        </div>

                        <div class="form-group title">
                            {{Form::label('placeId', 'Place Id:')}}
                            {{Form::text('placeId',null,array('class' => 'form-control',  'placeholder'=>'Place Id', 'id' => 'placeId'))}}
                        </div>

                        <div class="form-group title">
                            {{Form::label('iot_X', 'Iot X:')}}
                            {{Form::text('iot_X',null,array('class' => 'form-control',  'placeholder'=>'Iot X', 'id' => 'lat') )}}
                        </div>

                        <div class="form-group title">
                            {{Form::label('iot_Y', 'Iot Y:')}}
                            {{Form::text('iot_Y',null,array('class' => 'form-control',  'placeholder'=>'Iot Y', 'id' => 'long'))}}
                        </div>

                        <div class="form-group title">
                            {{Form::label('uuid', 'UUID:*')}}
                            {{Form::text('uuid',null,array('class' => 'form-control', 'placeholder'=>'UUId'))}}
                        </div>

                        {{Form::hidden('device_type',"iot")}}

                        <div class="form-group">
                            <div class="pull-right">
                                {{Form::submit('Submit',array('class' => 'btn btn-primary'))}}
                                <a href="{{ route('iot.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
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
        var input = document.getElementById('location');
        var autocomplete = new google.maps.places.Autocomplete(input);

        $("#location").change(function(){
            var geocoder = new google.maps.Geocoder();
            var address = document.getElementById("location").value;
            //console.log(address);
            geocoder.geocode( { 'address': address}, function(results, status) {

                //console.log(status);
                if (status == google.maps.GeocoderStatus.OK) {

                    var lat  = results[0].geometry.location.lat();
                    var long = results[0].geometry.location.lng();

                    var placeId = results[0].place_id;

                    //console.log(lat);
                    //console.log(long);

                    jQuery('#lat').val(lat);
                    jQuery('#long').val(long);
                    jQuery('#placeId').val(placeId);
                }
            });
        });
    });

</script>
@endsection
