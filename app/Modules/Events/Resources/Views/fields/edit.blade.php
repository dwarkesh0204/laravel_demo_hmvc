@extends('events::layouts.app')
@section('javascript')
<script type="text/javascript">
    jQuery(document).ready(function() {

        var valueArray = ["dropdown", "checkbox", "radio"];
        var fieldType  = jQuery('#field_type').val();

        if(valueArray.indexOf(fieldType) !== -1){
            jQuery('.items').css('display', 'block');
        }

        if(fieldType == 'dropdown'){
            jQuery('.multiple').css('display', 'block');
        }

        jQuery(".title" ).focusout(function(event) {
            var titleElement = jQuery(this).children('#display_name');
            var value        = jQuery(titleElement).val();
            var slugValue    = value.replace(/\s+/g, '_').toLowerCase();
            if(slugValue != ""){
                var slugValue    = 'field_' + slugValue;
                var slugCss      = jQuery('.slug').css('display');
                jQuery('.slug').children('input').val(slugValue);
                if(slugCss == 'none'){
                    jQuery('.slug').css('display', 'block');
                }
            }
        });

        jQuery('#field_type').change(function(event) {
            var selectedValue = jQuery(this).val();
            var itemCss       = jQuery('.items').css('display');
            var multiCss      = jQuery('.multiple').css('display');

            if(valueArray.indexOf(selectedValue) !== -1){
                if(itemCss == 'none'){
                    jQuery('.items').css('display', 'block');
                }
                if(selectedValue == 'dropdown'){
                    jQuery('.multiple').css('display', 'block');
                }else{
                    jQuery('.multiple').css('display', 'none');
                }
            }else{
                if(itemCss == 'block'){
                    jQuery('.items').css('display', 'none');
                }

                if(multiCss == 'block'){
                    jQuery('.multiple').css('display', 'none');
                }
            }
        });

        jQuery(document).on('change', '.type', function() {

            var selectedValue = jQuery(this).val();

            if(selectedValue == 'register')
            {
                jQuery('.events').css('display', 'none');
                jQuery('.roles').css('display', 'none');
            }
            if(selectedValue == 'events')
            {
                jQuery('.events').css('display', 'block');
                jQuery('.roles').css('display', 'none');
            }
            if(selectedValue == 'roles')
            {
                jQuery('.events').css('display', 'none');
                jQuery('.roles').css('display', 'block');
            }
            if(selectedValue == '0')
            {
                jQuery('.events').css('display', 'none');
                jQuery('.roles').css('display', 'none');
            }
        });

        var seltypeVal = jQuery(".type").val();

        if(seltypeVal == 'roles')
        {
            jQuery('.roles').css('display', 'block');
        }
        if(seltypeVal == 'events')
        {
            jQuery('.events').css('display', 'block');
        }


    });

    jQuery(document).on('load', function(event) {
        var valueArray = ["dropdown", "checkbox", "radio"];

        var fieldType = jQuery('#field_type').val();
        console.log(fieldType);

        if(valueArray.indexOf(fieldType) !== -1){
            jQuery('.items').css('display', 'block');
        }

        if(fieldType == 'dropdown'){
            jQuery('.multiple').css('display', 'block');
        }
    });
</script>
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
                <strong>System Fields</strong>
            </div>
            <div class="panel-body">
                <div class="col-md-6 col-md-offset-3">
                {!! Form::model($item, ['method' => 'PATCH','route' => ['fields.update', $item->id]]) !!}
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group title">
                                {{Form::label('display_name', 'Caption*:')}}
                                {{Form::text('display_name',null,array('class' => 'form-control'))}}
                            </div>

                            <div class="form-group slug">
                                {{Form::label('field_name', 'Name:')}}
                                {{Form::text('field_name',null,array('class' => 'form-control'))}}
                            </div>

                            <div class="form-group">
                                {!! Form::label('group_id', 'Group*:', ['class' => 'control-label']) !!}
                                {!! Form::select('group_id', $groups, null, ['class' => 'form-control']) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::label('field_type', 'Select Type*:', ['class' => 'control-label']) !!}
                                {!! Form::select('field_type',
                                array('' => 'Select', 'text' => 'Textbox', 'textarea' => 'Textarea', 'dropdown' => 'Dropdown', 'checkbox' => 'Checkbox Group', 'radio' => 'Radio Group', 'password' => 'Password', 'file' => 'File Upload', 'email' => 'Email Address'), null, ['class' => 'form-control']) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::label('type', 'Select Field For*:', ['class' => 'control-label']) !!}
                                {!! Form::select('type',
                                array('0' => 'Select', 'register' => 'Register', 'events' => 'Events', 'roles' => 'Roles'), null, ['class' => 'form-control type']) !!}
                            </div>

                            <div class="form-group events" style="display:none;">
                                {!! Form::label('event_id', 'Events*:', ['class' => 'control-label']) !!}
                                {!! Form::select('event_id[]', $events, $event_id, ['class' => 'form-control', 'multiple' => 'multiple']) !!}
                            </div>

                            <div class="form-group roles" style="display:none;">
                                {!! Form::label('role_id', 'Roles*:', ['class' => 'control-label']) !!}
                                {!! Form::select('role_id[]', $roles, $role_id, ['class' => 'form-control', 'multiple' => 'multiple']) !!}
                            </div>

                            <div class="form-group items" style="display: none">
                                {{Form::label('items', 'Items:')}}
                                {{Form::textarea('items',null,array('class' => 'form-control','rows' => '4', 'cols' => '50'))}}
                            </div>

                            <div class="form-group">
                                {{Form::label('default', 'Default:')}}
                                {{Form::text('default',null,array('class' => 'form-control'))}}
                            </div>

                            <div class="form-group">
                                {!! Form::label('validation_rule', 'Validation Rule:', ['class' => 'control-label']) !!}
                                {!! Form::select('validation_rule', array('' => 'No Validation', 'alpha' => 'Alpha',
                                'numeric' => 'Numeric', 'alphanumeric' => 'Alphanumeric', 'mobile_number' => 'Mobile Number', 'email' => 'Email'), null,
                                ['class' => 'form-control']) !!}
                            </div>

                             <div class="form-group multiple" style="display: none">
                                <div class="form-inline">
                                    {{Form::label('multiple', 'Multiple:')}}
                                    <div class="radio">
                                        {{ Form::radio('multiple', '1', false)}}
                                        {{ Form::label('Yes', 'Yes')}}
                                    </div>
                                    <div class="radio">
                                        {{ Form::radio('multiple', '0', true)}}
                                        {{ Form::label('No', 'No') }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="form-inline">
                                    {{Form::label('active', 'Active:')}}
                                    <div class="radio">
                                        {{ Form::radio('active', '1', true)}}
                                        {{ Form::label('Yes', 'Yes')}}
                                    </div>
                                    <div class="radio">
                                        {{ Form::radio('active', '0', false)}}
                                        {{ Form::label('No', 'No') }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="form-inline">
                                    {{Form::label('required', 'Required:')}}
                                    <div class="radio">
                                        {{ Form::radio('required', '1', true)}}
                                        {{ Form::label('Yes', 'Yes')}}
                                    </div>
                                    <div class="radio">
                                        {{ Form::radio('required', '0', false)}}
                                        {{ Form::label('No', 'No') }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="form-inline">
                                    {{Form::label('searchable', 'Searchable:')}}
                                    <div class="radio">
                                        {{ Form::radio('searchable', '1', false)}}
                                        {{ Form::label('Yes', 'Yes')}}
                                    </div>
                                    <div class="radio">
                                        {{ Form::radio('searchable', '0', true)}}
                                        {{ Form::label('No', 'No') }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="form-inline">
                                    {{Form::label('filterable', 'Filterable:')}}
                                    <div class="radio">
                                        {{ Form::radio('filterable', '1', false)}}
                                        {{ Form::label('Yes', 'Yes')}}
                                    </div>
                                    <div class="radio">
                                        {{ Form::radio('filterable', '0', true)}}
                                        {{ Form::label('No', 'No') }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="form-inline">
                                    {{Form::label('privacy', 'Privacy:')}}
                                    <div class="radio">
                                        {{ Form::radio('privacy', '1', false)}}
                                        {{ Form::label('Yes', 'Yes')}}
                                    </div>
                                    <div class="radio">
                                        {{ Form::radio('privacy', '0', true)}}
                                        {{ Form::label('No', 'No') }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="pull-right">
                                    {{Form::submit('Submit',array('class' => 'btn btn-primary'))}}
                                    <a href="{{ route('fields.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
                                </div>
                            </div>
                        </div>
                    </div>
                {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
