@extends('events::layouts.app')
@section('javascript')
@endsection
<style type="text/css">
/*.glyphicon-plus-sign{cursor: pointer; margin-right: 30px; margin-top: 5px;}
.glyphicon-minus-sign{display: inline-block; cursor: pointer; margin-right: 30px; margin-top: 18px;}*/
</style>
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
            {!! Form::open(array('route' => 'events.store','method'=>'POST', 'files' => 'true')) !!}
            <div id="tab" class="panel-heading" data-toggle="buttons-radio">
                <a href="#event_detail" class="btn active" data-toggle="tab"><strong>Event</strong></a>
                <!-- <a href="#event_fields" class="btn" data-toggle="tab"><strong>Event Fields</strong></a> -->
            </div>
            <div class="tab-content">
            	<div class="panel-body tab-pane active" id="event_detail">
            		<div class="box-body">
            			<div class="form-group row">
            				{{Form::label('name', 'Title:*', ['class' => 'col-md-2 col-form-label'])}}
            				<div class="col-md-5">
            					{{Form::text('name',null,array('class' => 'form-control'))}}
            				</div>
            			</div>
            			<div class="form-group row">
            				{{Form::label('shortdesc', 'Short Description:', ['class' => 'col-md-2 col-form-label'])}}
            				<div class="col-md-10">
            					{{Form::textarea('shortdesc',null,array('class' => 'form-control', 'rows' => '4', 'cols'=> '50'))}}
            				</div>
            			</div>
            			<div class="form-group row">
            				{{Form::label('description', 'Description:', ['class' => 'col-md-2 col-form-label'])}}
            				<div class="col-md-10">
            					{{Form::textarea('description',null,array('class' => 'form-control', 'rows' => '8', 'cols'=> '100'))}}
            				</div>
            			</div>
            			<div class="form-group row">
            				{{Form::label('status', 'Active:', ['class' => 'col-md-2 col-form-label'])}}
            				<div class="form-inline col-md-3">
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
            			<div class="form-group row">
            				{{Form::label('startdate', 'Start Date*:', ['class' => 'col-md-2 col-form-label'])}}
            				<div class="col-md-3">
            					{{Form::text('startdate', null, ['class' => 'form-control', 'id' => 'start_date'])}}
            				</div>
            				<div class="col-md-2">
            					{{Form::text('starttime', null, ['class' => 'form-control', 'id' => 'start_time'])}}
            				</div>
            			</div>
            			<div class="form-group row">
            				{{Form::label('enddate', 'End Date*:', ['class' => 'col-md-2 col-form-label'])}}
            				<div class="col-md-3">
            					{{Form::text('enddate', null, ['class' => 'form-control', 'id' => 'end_date'])}}
            				</div>
            				<div class="col-md-2">
            					{{Form::text('endtime', null, ['class' => 'form-control', 'id' => 'end_time'])}}
            				</div>
            			</div>
            			<div class="form-group row">
            				{{Form::label('cover_image', 'Cover Photo:', ['class' => 'col-md-2 col-form-label'])}}
            				<div class="col-md-2">
            					{{Form::file('cover_image')}}
            				</div>
            			</div>
            			{{--<div class="form-group row gallery-section">
            				{{Form::label('gallery', 'Gallery:', ['class' => 'col-md-2 col-form-label'])}}
            				<div class="col-md-3">
            					{{Form::file('gallery[]')}}
            				</div>
            				<div class="col-md-1">
            					<i class="glyphicon glyphicon-plus-sign pull-right"></i>
            				</div>
            			</div>--}}
            			<div class="form-group row">
            				{{Form::label('type', 'Type:', ['class' => 'col-md-2 col-form-label'])}}
            				<div class="form-inline col-md-3">
            					<div class="radio">
            						{{ Form::radio('type', 'inviteonly', true)}}
            						{{ Form::label('Inviteonly', 'Inviteonly')}}
            					</div>
            					<div class="radio">
			            			{{ Form::radio('type', 'open', false)}}
			            			{{ Form::label('Open', 'Open') }}
            					</div>
            				</div>
            			</div>
            			<div class="form-group row">
                            {{Form::label('price', 'Price:', ['class' => 'col-md-2 col-form-label'])}}
                            <div class="col-md-3">
                                {{Form::text('price', null, ['class' => 'form-control'])}}
                            </div>
                        </div>
                        <div class="form-group row">
                            {{Form::label('expected_attendaees', 'Expected Attendaees:', ['class' => 'col-md-2 col-form-label'])}}
                            <div class="col-md-3">
                                {{Form::text('expected_attendaees', null, ['class' => 'form-control'])}}
                            </div>
                        </div>
                        <div class="form-group row">
                            {{Form::label('venue', 'Venue Address:', ['class' => 'col-md-2 col-form-label'])}}
                            <div class="col-md-5">
                                {{Form::text('venue', null, ['class' => 'form-control', 'id' => 'venue_address', 'placeholder' => 'Enter Venue address'])}}
                            </div>
                        </div>
                        <div class="form-group row">
                            {{Form::label('phone_number', 'Phone Number:', ['class' => 'col-md-2 col-form-label'])}}
                            <div class="col-md-3">
                                {{Form::text('phone_number', null, ['class' => 'form-control'])}}
                            </div>
                        </div>
                        <div class="form-group row">
                            {{Form::label('email', 'Email:', ['class' => 'col-md-2 col-form-label'])}}
                            <div class="col-md-3">
                                {{Form::text('email', null, ['class' => 'form-control'])}}
                            </div>
                        </div>
                        <div class="form-group row">
                            {{Form::label('entrypass', 'Entry Pass:', ['class' => 'col-md-2 col-form-label'])}}
                            <div class="col-md-5">
                                {{ Form::checkbox('entrypass', '1')}}
                                Required
                            </div>
                        </div>
                        <div class="form-group row">
                            {{Form::label('pagetype', 'Type:', ['class' => 'col-md-2 col-form-label'])}}
                            <div class="form-inline col-md-3">
                                <div class="radio pagetype">
                                    {{ Form::radio('pagetype', 'url', true)}}
                                    {{ Form::label('Url', 'Url') }}
                                </div>
                                <div class="radio pagetype">
                                    {{ Form::radio('pagetype', 'html', false)}}
                                    {{ Form::label('Html', 'Html')}}
                                </div>
                            </div>
                        </div>
                        <div class="form-group row page-url">
                            {{Form::label('url', 'Cms Page Url:', ['class' => 'col-md-2 col-form-label'])}}
                            <div class="col-md-5">
                                {{Form::url('url',null,array('class' => 'form-control'))}}
                            </div>
                        </div>
                        <div class="form-group page-html" style="display:none;">
                            {{Form::label('html', 'Html Content:')}}
                            {{Form::textarea('html',null,array('class' => 'form-control','id' => 'technig'))}}
                        </div>
                        <div class="form-group row meals">
                            {{Form::label('meals', 'Meal Type:', ['class' => 'col-md-2 col-form-label'])}}
                            <div class="form-inline col-md-3">
                                <div class="radio">
                                    {{ Form::radio('meals', 'Free', true)}}
                                    {{ Form::label('Free', 'Free')}}
                                </div>
                                <div class="radio">
                                    {{ Form::radio('meals', 'Paid', false)}}
                                    {{ Form::label('Paid', 'Paid') }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <strong>Roles:</strong>
                            <br/>
                            @foreach($roles as $value)
                                <label>
                                    {{ Form::checkbox('roles[]', $value->id, false, array('class' => 'name')) }}
                                    {{ $value->name }}
                                </label>
                                <br/>
                            @endforeach
                        </div>

                        <div class="form-group">
                            {{Form::hidden('latitude', null, ['id' => 'latitude'])}}
                            {{Form::hidden('longitude', null, ['id' => 'longitude'])}}

                            <!-- <div class="pull-right">
                                <a href="#event_fields" class="btn" data-toggle="tab"><button type="button" class="btn btn-primary">Next</button></a>
                            </div> -->

                        </div>

                        <div class="col-lg-12">
                            <div class="pull-right">
                                {{Form::submit('Submit',array('class' => 'btn btn-primary'))}}
                                <a href="{{ route('events.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
                            </div>
                        </div>
                        {!! Form::close() !!}

                    </div>
                </div>

                <div class="tab-pane" id="event_fields">
                    <div class="row clearfix">
                        <div class="col-md-12 column">
                            <table class="table table-bordered table-hover" id="tab_logic">
                                <tbody>
                                    <tr id='addr0'>
                                        <td>1</td>
                                        <td>
                                            {{Form::label('display_name', 'Caption*:')}}
                                            {{Form::text('fields[0][display_name]',null,array('class' => 'form-control'))}}
                                        </td>
                                        <td>
                                            {!! Form::label('field_type', 'Select Type*:', ['class' => 'control-label']) !!}
                                            {!! Form::select('fields[0][field_type]', array('' => 'Select', 'text' => 'Textbox', 'textarea' => 'Textarea', 'dropdown' => 'Dropdown', 'checkbox' => 'Checkbox Group', 'radio' => 'Radio Group', 'password' => 'Password', 'file' => 'File Upload','email' => 'Email Address'), null, ['class' => 'form-control field_type','id' => 'field_type']) !!}
                                        </td>
                                        <td class="items" style="display: none">
                                            {{Form::label('items', 'Items:')}}
                                            {{Form::text('fields[0][items]',null,array('class' => 'form-control'))}}
                                        </td>
                                        <td class="multiple" style="display: none">
                                            {{Form::label('multiple', 'Multiple:')}}
                                            {{ Form::radio('fields[0][multiple]', '1', false)}}
                                            {{ Form::label('Yes', 'Yes')}}
                                            {{ Form::radio('fields[0][multiple]', '0', true)}}
                                            {{ Form::label('No', 'No') }}
                                        </td>
                                        <td colspan="2">
                                            {{Form::label('active', 'Active:')}}
                                            {{ Form::radio('fields[0][active]', '1', true)}}
                                            {{ Form::label('Yes', 'Yes')}}
                                            {{ Form::radio('fields[0][active]', '0', false)}}
                                            {{ Form::label('No', 'No') }}
                                        </td>
                                        <td colspan="3">
                                            {{Form::label('required', 'Required:')}}
                                            {{ Form::radio('fields[0][required]', '1', true)}}
                                            {{ Form::label('Yes', 'Yes')}}
                                            {{ Form::radio('fields[0][required]', '0', false)}}
                                            {{ Form::label('No', 'No') }}
                                        </td>
                                    </tr>
                                    <tr id='addr1'></tr>
                                </tbody>
                            </table>
                            <div class="col-lg-12">
                                <div class="pull-right">
                                    <a class="btn btn-default" id="add_row" >Add Row</a>
                                    <a class="btn btn-default" id='delete_row' >Delete Row</a>
                                </div>
                            </div>

                            <!-- <div class="col-lg-12">
                                <div class="pull-right">
                                    {{Form::submit('Submit',array('class' => 'btn btn-primary'))}}
                                    <a href="{{ route('events.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
                                </div>
                            </div>
                            {!! Form::close() !!} -->
                        </div>
                    </div>

                </div>
            </div>

        </div>
	</div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#technig').summernote({
            height:300,
        });

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
            interval: 30,
            scrollbar: true
        });

        $('#end_time').timepicker({
            timeFormat: 'HH:mm',
            interval: 30,
            scrollbar: true
        });


        $('.pagetype').children('input').click(function(event){
            var selectedValue = jQuery(this).val();
            if (selectedValue == 'html'){
                jQuery(".page-url").fadeOut('slow');
                jQuery(".page-html").fadeIn('slow');
            }else if(selectedValue == 'url'){
                jQuery(".page-html").fadeOut('slow');
                jQuery(".page-url").fadeIn('slow');
            }
        });

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

        /*jQuery('.glyphicon-plus-sign').click(function(event) {
         var sectionValue = jQuery(".gallery-section").find('input[type=file]').val();
         if (sectionValue){
         var section = '<div class="form-group row custom-section"> <label for="gallery" class="col-md-2 col-form-label"></label> <div class="col-md-3"> <input name="gallery[]" type="file"></div> <div class="col-md-1"><i class="glyphicon glyphicon-minus-sign pull-right"></i></div> </div>';
         jQuery(section).insertAfter('.gallery-section');
         var imageValue = jQuery(".gallery-section").find('input[type=file]').val();
         console.log(jQuery('.gallery-section').next('.custom-section').find('input[type=file]'));
         jQuery('.gallery-section').next('.custom-section').find('input[type=file]').val(imageValue);
         //jQuery('.gallary-section').val('');*!/
         }else{
         alert('Please Select Image.');
         }
         });

         jQuery(document).on('click', '.glyphicon-minus-sign', function(event) {
         jQuery(this).closest('.custom-section').remove();
         jQuery(this).remove();
         event.preventDefault();
         });*/
    });

    // Custome fields related code start here...
    jQuery(document).ready(function($){
        var i=1;
        $("#add_row").click(function(){
            jQuery('#addr'+i).html("<td>"+ (i+1) +"</td><td><label for='display_name'>Caption*:</label><input class='form-control' name='fields["+i+"][display_name]' type='text'/></td><td><label for='field_type' class='control-label'>Select Type*:</label><select class='form-control field_type' id='field_type"+i+"' name='fields["+i+"][field_type]'><option value='--' selected='selected'>Select</option><option value='text'>Textbox</option><option value='textarea'>Textarea</option><option value='dropdown'>Dropdown</option><option value='checkbox'>Checkbox Group</option><option value='radio'>Radio Group</option><option value='password'>Password</option><option value='file'>File Upload</option><option value='email'>Email Address</option></select></td><td class='items' style='display: none'><label for='items'>Items:</label><input class='form-control' name='fields["+i+"][items]' id='items' type='text'></td><td class='multiple' style='display: none'><label for='multiple'>Multiple:</label><input name='fields["+i+"][multiple]' value='1' id='multiple' type='radio'><label for='Yes'>Yes</label><input checked='checked' name='fields["+i+"][multiple]'' value='0' id='multiple' type='radio'><label for='No'>No</label></td><td colspan='2'><label for='active'>Active:</label><input checked='checked' name='fields["+i+"][active]' value='1' id='active' type='radio'><label for='Yes'>Yes</label><input name='fields["+i+"][active]'' value='0' id='active' type='radio'><label for='No'>No</label></td><td colspan='3'><label for='required'>Required:</label><input checked='checked' name='fields["+i+"][required]' value='1' id='required' type='radio'><label for='Yes'>Yes</label><input name='fields["+i+"][required]' value='0' id='required' type='radio'><label for='No'>No</label></td>");

            jQuery('#tab_logic').append('<tr id="addr'+(i+1)+'"></tr>');
            i++;
        });
        $("#delete_row").click(function(){
            if(i>1){
                jQuery("#addr"+(i-1)).html('');
                i--;
            }
        });
    });

    jQuery(document).ready(function($) {

        var valueArray = ["dropdown", "checkbox", "radio"];
        var fieldType  = jQuery('.field_type').val();
        if(valueArray.indexOf(fieldType) !== -1){
            jQuery('.items').css('display', 'table-cell');
            jQuery('.itemsHeader').css('display', 'table-cell');

        }

        if(fieldType == 'dropdown'){
            jQuery('.multiple').css('display', 'table-cell');
            jQuery('.multipleHeader').css('display', 'table-cell');

        }

        $(document).on('change', '.field_type', function() {
            // Does some stuff and logs the event to the console
            var selectedValue = jQuery(this).val();
            var itemCss       = jQuery(this).parent('td').next('.items').css('display');
            var multiCss      = jQuery(this).parent('td').siblings('.multiple').css('display');

            if(valueArray.indexOf(selectedValue) !== -1){
                if(itemCss == 'none'){
                    jQuery(this).parent('td').next('.items').css('display', 'table-cell');

                    if(selectedValue == 'dropdown'){
                        //jQuery(this).parent('td').next('.multiple').css('display', 'table-cell');
                         jQuery(this).parent('td').siblings('.multiple').css('display', 'table-cell');
                    }else{
                        jQuery(this).parent('td').siblings('.multiple').css('display', 'none');
                    }
                }
            }else{
                if(itemCss == 'table-cell'){
                    jQuery(this).parent('td').next('.items').css('display', 'none');
                }

                if(multiCss == 'table-cell'){
                    jQuery(this).parent('td').siblings('.multiple').css('display', 'none');
                }
            }
        });
    });
</script>

@endsection

