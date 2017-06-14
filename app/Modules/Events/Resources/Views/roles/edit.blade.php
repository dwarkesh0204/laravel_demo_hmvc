@extends('events::layouts.app')
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-lg-12 margin-tb">
                <div class="pull-left">
                    <h2>Edit Role</h2>
                </div>
            </div>

        </div>
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
        {!! Form::model($role, ['method' => 'PATCH','route' => ['roles.update', $role->id]]) !!}
        <div class="row">
            <div class="span12">
                <div id="tab" class="btn-group" data-toggle="buttons-radio">
                    <a href="#roles_detail" class="btn active" data-toggle="tab">Roles Detail</a>
                    <!--
                    <a href="#roles_fields" class="btn" data-toggle="tab">Roles Fields</a>
                    -->
                </div>
                <div class="tab-content">
                    <div class="tab-pane active" id="roles_detail">

                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>Name:</strong>
                                {!! Form::text('display_name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>Description:</strong>
                                {!! Form::textarea('description', null, array('placeholder' => 'Description','class' => 'form-control','style'=>'height:100px')) !!}
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>Permission:</strong>
                                <br/>
                                @foreach($permission as $value)
                                    <label>{{ Form::checkbox('permission[]', $value->id, in_array($value->id, $rolePermissions) ? true : false, array('class' => 'name')) }}
                                        {{ $value->display_name }}</label>
                                    <br/>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="pull-right">
                                <button type="submit" class="btn btn-primary">Submit</button>
                                <a href="{{ route('roles.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
                            </div>
                        </div>
                        {!! Form::close() !!}

                        <!--
                        <div class="col-lg-12">
                            <a href="#roles_fields" class="btn pull-right" data-toggle="tab">
                                <button type="button" class="btn btn-primary">Next</button>
                            </a>
                        </div>
                        -->
                    </div>

                    <div class="tab-pane" id="roles_fields">
                        <div class="row clearfix">
                            <div class="col-md-12 column">
                                <table class="table table-bordered table-hover" id="tab_logic">
                                    <p id="count" style="display:none">{{ count($rolefields) }}</p>
                                    <tbody>
                                    @if(count($rolefields))
                                        @foreach($rolefields as $key => $rolefield)
                                            {{Form::hidden('fields['.$key.'][id]',$rolefield->id,array('id' => 'fields['.$key.'][id]'))}}
                                            <tr id='addr{{ $key }}'>
                                                <td>
                                                    {{$key}}
                                                </td>
                                                <td>
                                                    {{Form::label('display_name', 'Caption*:')}}
                                                    {{Form::text('fields['.$key.'][display_name]',$rolefield->display_name,array('class' => 'form-control'))}}
                                                </td>
                                                <td>
                                                    {!! Form::label('field_type', 'Select Type*:', ['class' => 'control-label']) !!}
                                                    {!! Form::select('fields['.$key.'][field_type]',
                                                    array('' => 'Select', 'text' => 'Textbox', 'textarea' => 'Textarea', 'dropdown' => 'Dropdown', 'checkbox' => 'Checkbox Group', 'radio' => 'Radio Group', 'password' => 'Password', 'file' => 'File Upload','email' => 'Email Address'), $rolefield->field_type, ['class' => 'form-control field_type']) !!}
                                                </td>
                                                @if($rolefield->field_type == 'dropdown' || $rolefield->field_type == 'checkbox' || $rolefield->field_type == 'radio')
                                                    <td class="items">
                                                        {{Form::label('items', 'Items:')}}
                                                        {{Form::text('fields['.$key.'][items]',$rolefield->items, array('class' => 'form-control'))}}
                                                    </td>
                                                @else
                                                    <td class="items" style="display: none">
                                                        {{Form::label('items', 'Items:')}}
                                                        {{Form::text('fields['.$key.'][items]',null ,array('class' => 'form-control'))}}
                                                    </td>
                                                @endif
                                                @if($rolefield->field_type == 'dropdown' || $rolefield->field_type == 'checkbox' || $rolefield->field_type == 'radio')
                                                    <td class="multiple">
                                                        {{Form::label('multiple', 'Multiple:')}}
                                                        {{ Form::radio('fields['.$key.'][multiple]', '1', $rolefield->multiple == 1 ? 'ture' : '')}}
                                                        {{ Form::label('Yes', 'Yes')}}
                                                        {{ Form::radio('fields['.$key.'][multiple]', '0', $rolefield->multiple == 0 ? 'false' : '')}}
                                                        {{ Form::label('No', 'No') }}
                                                    </td>
                                                @else
                                                    <td class="multiple" style="display: none">
                                                        {{Form::label('multiple', 'Multiple:')}}
                                                        {{ Form::radio('fields['.$key.'][multiple]', '1', 'false')}}
                                                        {{ Form::label('Yes', 'Yes')}}
                                                        {{ Form::radio('fields['.$key.'][multiple]', '0', 'true')}}
                                                        {{ Form::label('No', 'No') }}
                                                    </td>
                                                @endif
                                                <td colspan="2">
                                                    {{Form::label('active', 'Active:')}}
                                                    {{ Form::radio('fields['.$key.'][active]', '1', $rolefield->active == 1 ? 'ture' : '')}}
                                                    {{ Form::label('Yes', 'Yes')}}
                                                    {{ Form::radio('fields['.$key.'][active]', '0', $rolefield->active == 0 ? 'false' : '')}}
                                                    {{ Form::label('No', 'No') }}
                                                </td>
                                                <td colspan="3">
                                                    {{Form::label('required', 'Required:')}}
                                                    {{ Form::radio('fields['.$key.'][required]','1', $rolefield->required === '1' ? 'true' : 'false')}}
                                                    {{ Form::label('Yes', 'Yes')}}
                                                    {{ Form::radio('fields['.$key.'][required]','0', $rolefield->required === '0' ? 'false' : 'true')}}
                                                    {{ Form::label('No', 'No') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr id='addr{{$key+1}}'></tr>
                                    @else
                                        <tr id='addr0'>
                                            <td>
                                                1
                                            </td>

                                            <td>
                                                {{Form::label('display_name', 'Caption*:')}}
                                                {{Form::text('fields[0][display_name]',null,array('class' => 'form-control'))}}
                                            </td>

                                            <td>
                                                {!! Form::label('field_type', 'Select Type*:', ['class' => 'control-label']) !!}
                                                {!! Form::select('fields[0][field_type]',
                                                array('' => 'Select', 'text' => 'Textbox', 'textarea' => 'Textarea', 'dropdown' => 'Dropdown', 'checkbox' => 'Checkbox Group', 'radio' => 'Radio Group', 'password' => 'Password', 'file' => 'File Upload','email' => 'Email Address'), null, ['class' => 'form-control field_type','id' => 'field_type']) !!}
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
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="pull-right">
                                <a id="add_row"><input type="button" class="btn btn-success" value="Add Row"></a>
                                <a id='delete_row'><input type="button" class="btn btn-danger" value="Delete Row"></a>
                            </div>
                        </div>

                        <!--
                        <div class="col-lg-12">
                            <div class="pull-right">
                                <button type="submit" class="btn btn-primary">Submit</button>
                                <a href="{{ route('roles.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
                            </div>
                        </div>
                        {{Form::hidden('deletedFieldId',null, array('id' => 'deletedFieldId') )}}
                        {!! Form::close() !!}
                        -->
                    </div>
                </div>
            </div>


        </div>

        {!! Form::close() !!}
    </div>
@endsection

@section('javascript')
    <script type="text/javascript">
        jQuery(document).ready(function () {

            var i = {{count($rolefields)}};
            var fieldIds = new Array();
            //var i=3;
            jQuery("#add_row").click(function () {
                jQuery('#addr' + i).html("<td>" + (i + 1) + "</td><td><label for='display_name'>Caption*:</label><input class='form-control' name='fields[" + i + "][display_name]' type='text'/></td><td><label for='field_type' class='control-label'>Select Type*:</label><select class='form-control field_type' id='field_type" + i + "' name='fields[" + i + "][field_type]'><option value='--' selected='selected'>Select</option><option value='text'>Textbox</option><option value='textarea'>Textarea</option><option value='dropdown'>Dropdown</option><option value='checkbox'>Checkbox Group</option><option value='radio'>Radio Group</option><option value='password'>Password</option><option value='file'>File Upload</option><option value='email'>Email Address</option></select></td><td class='items' style='display: none'><label for='items'>Items:</label><input class='form-control' name='fields[" + i + "][items]' id='items' type='text'></td><td class='multiple' style='display: none'><label for='multiple'>Multiple:</label><input name='fields[" + i + "][multiple]' value='1' id='multiple' type='radio'><label for='Yes'>Yes</label><input checked='checked' name='fields[" + i + "][multiple]'' value='0' id='multiple' type='radio'><label for='No'>No</label></td><td colspan='2'><label for='active'>Active:</label><input checked='checked' name='fields[" + i + "][active]' value='1' id='active' type='radio'><label for='Yes'>Yes</label><input name='fields[" + i + "][active]'' value='0' id='active' type='radio'><label for='No'>No</label></td><td colspan='3'><label for='required'>Required:</label><input checked='checked' name='fields[" + i + "][required]' value='1' id='required' type='radio'><label for='Yes'>Yes</label><input name='fields[" + i + "][required]' value='0' id='required' type='radio'><label for='No'>No</label></td>");

                jQuery('#tab_logic').append('<tr id="addr' + (i + 1) + '"></tr>');
                i++;
            });
            jQuery("#delete_row").click(function () {

                if (i > 1) {
                    var cnt = i - 1;
                    var fieldId = document.getElementById("fields[" + cnt + "][id]").value;
                    fieldIds.push(fieldId);
                    var fieldIdsStr = fieldIds.join(", ");
                    jQuery("#deletedFieldId").val(fieldIdsStr);

                    jQuery("#addr" + (i - 1)).html('');
                    i--;
                }
            });

        });

        jQuery(document).ready(function () {

            var valueArray = ["dropdown", "checkbox", "radio"];
            var fieldType = jQuery('.field_type').val();
            if (valueArray.indexOf(fieldType) !== -1) {
                jQuery('.items').css('display', 'table-cell');
                jQuery('.itemsHeader').css('display', 'table-cell');

            }

            if (fieldType == 'dropdown') {
                jQuery('.multiple').css('display', 'table-cell');
                jQuery('.multipleHeader').css('display', 'table-cell');

            }

            jQuery(document).on('change', '.field_type', function () {


                // Does some stuff and logs the event to the console
                //jQuery('.field_type').change(function(event) {
                var selectedValue = jQuery(this).val();
                var itemCss = jQuery(this).parent('td').next('.items').css('display');
                var multiCss = jQuery(this).parent('td').siblings('.multiple').css('display');

                if (valueArray.indexOf(selectedValue) !== -1) {
                    if (itemCss == 'none') {
                        jQuery(this).parent('td').next('.items').css('display', 'table-cell');

                        if (selectedValue == 'dropdown') {
                            //jQuery(this).parent('td').next('.multiple').css('display', 'table-cell');
                            jQuery(this).parent('td').siblings('.multiple').css('display', 'table-cell');
                        } else {
                            jQuery(this).parent('td').siblings('.multiple').css('display', 'none');
                        }
                    }
                } else {
                    if (itemCss == 'table-cell') {
                        jQuery(this).parent('td').next('.items').css('display', 'none');
                    }

                    if (multiCss == 'table-cell') {
                        jQuery(this).parent('td').siblings('.multiple').css('display', 'none');
                    }
                }
                //});
            });
        });
    </script>
@endsection