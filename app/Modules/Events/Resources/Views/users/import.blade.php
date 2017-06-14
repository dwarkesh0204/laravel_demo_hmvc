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
                <strong>Import Users</strong>
                <span class="back pull-right"></span>
            </div>
            <div class="panel-body">
                {!! Form::open(array('route' => 'users.uplodCSV','method'=>'POST','id'=>'uploadUserCSV', 'class'=>'uploadUserCSV', 'files'=>'true', 'name'=>'uploadUserCSV')) !!}
                <div class="row">
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <div class="form-group">
                            <strong>Role:</strong>

                                {!! Form::select('role_id', (['' => '- Select Role -'] + $roles), null, array('class' => 'form-control role_id')) !!}
                        </div>
                    </div>

                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <div class="form-group">
                            <strong>Import CSV:</strong>
                            {{Form::file('import')}}
                        </div>
                    </div>


                    <div class="col-lg-12">
                        <div class="pull-right">
                            <input type="submit" id="import" value="Import" class="submit btn btn-primary" />
                            <a href="{{ route('users.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>

            <!-- Mapping form statr here -->
            <div class="panel-body mappForm" style="display:none">
                {!! Form::open(array('route' => 'users.mapUserStore','method'=>'POST')) !!}
                <input type="hidden" name="roleId" id="roleId" value="">
                <div class="row">
                    <hr />
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <div class="form-group">
                                <strong>System Fields</strong>
                            </div>
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <div class="form-group">
                                <strong>CSV Headers</strong>
                            </div>
                        </div>
                    <hr />

                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <div class="form-group">
                            <strong>Name</strong>
                        </div>
                    </div>
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <div class="form-group">
                            {!! Form::select('name', (['' => '- Select Header -']), '', array('class' => 'form-control', 'id'=>'field_name')) !!}
                        </div>
                    </div>

                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <div class="form-group">
                            <strong>Email</strong>
                        </div>
                    </div>

                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <div class="form-group">
                            {!! Form::select('email', (['' => '- Select Header -']), '', array('class' => 'form-control', 'id'=>'field_email')) !!}
                        </div>
                    </div>

                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <div class="form-group">
                            <strong>Phone Number</strong>
                        </div>
                    </div>

                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <div class="form-group">
                            {!! Form::select('phone_number', (['' => '- Select Header -']), '', array('class' => 'form-control','id'=>'field_phone_number')) !!}
                        </div>
                    </div>

                    <div id='dynaForm'></div>


                    <div class="col-lg-12">
                        <div class="pull-right">
                            <input type="submit" class="btn btn-primary" value="Submit">
                            <a href="{{ route('users.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>

        </div>
    </div>
<script type="text/javascript">
jQuery(document).ready(function ($) {

    jQuery(document).on('change', '.role_id', function()
    {
        var roleId = jQuery(this).val();

        var selectedValue = jQuery(this).find("option:selected").text();

        alert('Are you sure you want import users in this role "'+selectedValue+'"');

        jQuery("#roleId").val( roleId );

    });


    jQuery("form.uploadUserCSV").on('submit',(function(e)
    {
        var CSRF_TOKEN = '{{ csrf_token() }}';
        e.preventDefault();
        jQuery.ajax({
            url  : '{{url('/uplodCSV')}}',
            type : 'POST',
            async: false,
            data: new FormData(this),
            dataType : 'JSON',
            contentType: false,       // The content type used when sending data to the server.
            cache: false,             // To unable request pages to be cached
            processData:false,        // To send DOMDocument or non processed data file it is set to false
            success : function(data)
            {
                if(data['success']  == 0)
                {
                    alert(data['message']);
                }
                else
                {

                    /*jQuery('#importbtn').hide();
                    jQuery('#clientNameforCust').css('display','none');
                    jQuery('#clientNameforCustmap').css('display','block');


                    jQuery('#SucessMsgCustomer').css('display','none');*/
                    jQuery('.mappForm').css('display','block');
                    jQuery('#field_name').find('option').remove().end().append(data['headerOptions']);
                    jQuery('#field_email').find('option').remove().end().append(data['headerOptions']);
                    jQuery('#field_phone_number').find('option').remove().end().append(data['headerOptions']);
                    jQuery("#dynaForm").html(data['dynaForm']);

                }
            }
        });
    }));
});
</script>
@endsection
