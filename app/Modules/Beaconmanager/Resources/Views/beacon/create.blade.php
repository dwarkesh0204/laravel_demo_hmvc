@extends('beaconmanager::layouts.app')
@section('javascript')
@endsection
@section('content')
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCa2HEyzmSepTQnzX9deSUhBj_G9x9cSpc&libraries=geometry,places&location=no"></script>
<div class="container">
    <div class="row">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>Add New Beacon</strong>
            </div>
            <div class="panel-body">
                {!! Form::open(array('route' => 'beacon.store','method'=>'POST','files'=>'true')) !!}
                    {{ Form::hidden('user_id', $user_id) }}
                    <div class="box-body">
                        <div class="form-group title">
                            {{Form::label('name', 'Beacon Name:*')}}
                            {{Form::text('name',null,array('class' => 'form-control', 'placeholder'=>'Beacon Name'))}}
                        </div>

                        <div class="form-group title">
                            {{Form::label('type', 'Type:')}}
                            {{Form::text('type',null,array('class' => 'form-control', 'placeholder'=>'Type'))}}
                        </div>

                        <div class="form-group title">
                            {{Form::label('macid', 'MAC Id:')}}
                            {{Form::text('macid',null,array('class' => 'form-control', 'placeholder'=>'MAC Id'))}}
                        </div>

                        <div class="form-group title">
                            {{Form::label('major_id', 'Major Id:')}}
                            {{Form::text('major_id',null,array('class' => 'form-control', 'placeholder'=>'Major Id'))}}
                        </div>

                        <div class="form-group title">
                            {{Form::label('minor_id', 'Minor Id:')}}
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
                            {{Form::label('beacon_X', 'Beacon X:')}}
                            {{Form::text('beacon_X',null,array('class' => 'form-control',  'placeholder'=>'Beacon X', 'id' => 'lat') )}}
                        </div>

                        <div class="form-group title">
                            {{Form::label('beacon_Y', 'Beacon Y:')}}
                            {{Form::text('beacon_Y',null,array('class' => 'form-control',  'placeholder'=>'Beacon Y', 'id' => 'long'))}}
                        </div>



                        <div class="form-group title">
                            {{Form::label('uuid', 'UU Id:')}}
                            {{Form::text('uuid',null,array('class' => 'form-control', 'placeholder'=>'UU Id'))}}
                        </div>

                        <div class="form-group">
                            {{Form::label('active', 'Active:')}}
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
                                <a href="{{ route('beacon.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
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
