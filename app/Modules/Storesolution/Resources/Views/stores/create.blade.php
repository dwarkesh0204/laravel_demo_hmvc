@extends('storesolution::layouts.app')
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
                <strong>Visitor</strong>
                <span class="back pull-right"></span>
            </div>
            <div class="panel-body">
                {!! Form::open(array('route' => 'stores.store','method'=>'POST','files' => 'true')) !!}
                <div class="box-body">
                    <div class="form-group">
                        {!! Form::label('name','Store Name:*') !!}<br />
                        {!! Form::text('name', null, array('placeholder' => 'Store Name','class' => 'form-control')) !!}
                    </div>
                     <div class="form-group">
                        {!! Form::label('description','Description:') !!}<br />
                        {!! Form::textarea('description', null, ['placeholder' => 'Description','class' => 'form-control']) !!}
                    </div>
                    <div class="form-group row">
                        {{Form::label('cover_image', 'Cover Photo:', ['class' => 'col-md-2 col-form-label'])}}
                        <div class="col-md-2">
                            {{Form::file('cover_image')}}
                        </div>
                    </div>
                    <div class="form-group row">
                        {{Form::label('venue', 'Venue Address:', ['class' => 'col-md-2 col-form-label'])}}
                        <div class="col-md-5">
                            {{Form::text('venue', null, ['class' => 'form-control', 'id' => 'venue_address', 'placeholder' => 'Enter Venue address'])}}
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('email','Email:') !!}<br />
                        {!! Form::text('email', null, array('placeholder' => 'Email Address','class' => 'form-control')) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('phone_no','Contact Number:') !!}<br />
                        {!! Form::text('phone_no', null, array('placeholder' => 'Contact Number','class' => 'form-control')) !!}
                    </div>
                    <div class="form-group section-floorplan">
                        {!! Form::label('sections', 'Sections:', ['class' => 'control-label']) !!}
                        {!! Form::text('sections', null, ['class' => 'form-control section-control']) !!}
                        <i class="glyphicon glyphicon-plus-sign pull-right"></i>
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
                    <div class="col-lg-12">
                        <div class="pull-right">
                            <input type="submit" class="btn btn-primary" value="Submit">
                            <a href="{{ route('stores.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
    <div class="modal fade" id="sectionModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Warning.</h4>
                </div>
                <div class="modal-body">
                     <p>Please add section name.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script>
    jQuery(document).ready(function($) {
        var input = document.getElementById('venue_address');
        var autocomplete = new google.maps.places.Autocomplete(input);

        $("#venue_address").change(function(){
            var geocoder = new google.maps.Geocoder();
            var address = document.getElementById("venue_address").value;
            //console.log(address);
            geocoder.geocode( { 'address': address}, function(results, status) {
                console.log(status);
                if (status == google.maps.GeocoderStatus.OK) {
                    var lat  = results[0].geometry.location.lat();
                    var long = results[0].geometry.location.lng();
                    console.log(lat);
                    console.log(long);
                    jQuery('#latitude').val(lat);
                    jQuery('#longitude').val(long);
                }
            });
        });

        jQuery('.glyphicon-plus-sign').click(function(event) {
            var sectionValue = jQuery(".section-control").val();
            if (sectionValue)
            {
                var section = '<input type="text" name="FloorplanAreas[]" class="form-control custom-section"><i class="glyphicon glyphicon-minus-sign pull-right"></i>';
                jQuery(section).insertAfter('.glyphicon-plus-sign');
                var sectionValue = jQuery(".section-control").val();
                jQuery('.section-control').nextAll().eq(1).val(sectionValue);
                jQuery(".section-control").val('');
            }else{
                jQuery('#sectionModal').modal('show');
            }
        });

        jQuery(document).on('click', '.glyphicon-minus-sign', function(event) {
            jQuery(this).prev('.custom-section').remove();
            jQuery(this).remove();
            event.preventDefault();
        });
    });
</script>
@endsection