@extends('events::layouts.app')
@section('javascript')
    <style type="text/css">
        .dataTables_wrapper {
            margin-top: 50px;
        }

        .btn-micro {
            background-color: #c0c0c0;
        }
    </style>
@endsection
@section('content')
    @if(Session::has('flash_message'))
        <div class="alert alert-success">
            {{ Session::get('flash_message') }}
        </div>
    @endif
    @if(Session::has('delete_message'))
        <div class="alert alert-danger">
            {{ Session::get('delete_message') }}
        </div>
    @endif
    <div class="container">
        <div class="row">
            <div class="panel panel-default">
                <div class="panel-heading">Form Fields</div>
                <div class="panel-body">
                    <div class="col-md-6">

                        <a class="btn btn-success" data-toggle="modal" data-type="GroupAdd" data-target="#GroupModal">Create New Group</a>

                        <a class="btn btn-success" href="{{ route('fields.create') }}"> Create New Field</a>

                        <input type="button" class="btn btn-danger" id="deleteField" value="Delete">
                        {!! Form::open(['method' => 'DELETE','route' => ['fields.destroy', 1],'style'=>'display:inline', 'id' => 'deleteFieldsForm']) !!}
                        <input type="hidden" name="fieldsIDs" id="fieldvalue" value="">
                        {!! Form::close() !!}
                    </div>

                    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered"
                           id="cmsfieldsTable">
                        <thead>
                        <tr>
                            <th><input name="select_all" type="checkbox"></th>
                            <th>Title</th>
                            <th>Status</th>
                            <th style="display: none;">Group</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td><input type="checkbox" name="select_one" value="{{$item->id}}" class="select_one">
                                </td>
                                <td><a href="{{ route('fields.edit',$item->id) }}">{{ $item->display_name }}</a></td>
                                <td align="center">
                                    @if($item->active == 1)
                                        {!! Form::open(['route' => ['fields.changeStatus', $item->id],'style'=>'display:inline']) !!}
                                        <a class="btn btn-micro active" href="javascript:void(0);" onclick="" title=""
                                           data-original-title="Unpublish Item">
                                            <span class="glyphicon glyphicon-ok" aria-hidden="false"
                                                  style="color: green; font-size: 15px;"></span>
                                            <input type="hidden" name="mode" id="statusmode" value="unpublish">
                                        </a>
                                        {!! Form::close() !!}

                                    @else
                                        {!! Form::open(['route' => ['fields.changeStatus', $item->id],'style'=>'display:inline']) !!}
                                        <a class="btn btn-micro active" href="javascript:void(0);" onclick="" title=""
                                           data-original-title="Publish Item">
                                            <span class="glyphicon glyphicon-remove" aria-hidden="false"
                                                  style="color: red; font-size: 15px;"></span>
                                            <input type="hidden" name="mode" id="statusmode" value="publish">
                                        </a>
                                        {!! Form::close() !!}
                                    @endif
                                </td>
                                <td class="{{$item->group_id}}" style="display: none;">{{ $item->group_name }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add modal code for create and update ticket start here-->
    <div class="modal fade" id="GroupModal" tabindex="-1" role="dialog" aria-labelledby="GroupModalLabel">
        <div class="modal-dialog" role="ticket">
            <div class="modal-content">
                <div class="modal-header">
                    <span id="head-title">Add Group</span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    {!! Form::open(['route' => ['fields.storeGroup'], 'id' => 'StoreGroup', 'name' => 'StoreGroup', 'method' => 'post', 'files' => 'true'] ) !!}

                    <input type="hidden" name="field_type" id="field_type" value="group">
                    <input type="hidden" name="type" id="type" value="group">
                    <input type="hidden" name="group_id" id="group_id" value="">
                    <input type="hidden" name="task" id="task" value="">

                        <div class="form-group title">
                            {{Form::label('display_name', 'Caption:*')}}
                            {{Form::text('display_name',null,array('class' => 'form-control', 'id' => 'display_name'))}}
                        </div>
                        <div class="form-group">
                            {{Form::label('active', 'Active:')}}
                            <div class="form-inline">
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
                    {!! Form::close() !!}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default store-group" data-dismiss="modal">Save</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Add modal code for create and update ticket start here-->

    <script type="text/javascript">

        jQuery(document).ready(function ($) {
            var FieldsIDs = [];
            $('#cmsfieldsTable').DataTable({
                select: true,
                "aoColumnDefs": [
                    {"bSortable": false, "aTargets": [0]},
                    { "width": "2%", "targets": 0 },
                    { "width": "8%", "targets": 2 },
                ],

                "drawCallback": function ( settings ) {
                    var api  = this.api();
                    var rows = api.rows( {page:'current'} ).nodes();
                    var last = null;

                    api.column(3, {page:'current'} ).data().each( function ( group, i )
                    {
                        if ( last !== group ) {
                            var group_id = $(rows[i]).find("td").last().attr("class");
                            $(rows).eq( i ).before(
                                '<tr class="group"><td><input type="checkbox" name="select_one" value="'+group_id+'" class="select_one"></td><td colspan="3"><b><a style="cursor: pointer;" onclick="updateGroupDetail(\''+group_id+'\')">'+group+'</a></b></td></tr>'
                            );

                            last = group;
                        }
                    } );
                }
            });
            jQuery("th:first").removeClass('sorting_asc');

            jQuery('td input[name="select_one"]').on('click', function (e) {
                if (this.checked) {
                    var FieldID = jQuery(this).val();
                    FieldsIDs.push(FieldID);
                    jQuery('#fieldvalue').val(FieldsIDs);
                } else {
                    var FieldID = jQuery(this).val();
                    var removeIndex = jQuery.inArray(FieldID, FieldsIDs);
                    FieldsIDs.splice(removeIndex, 1);
                    jQuery('#fieldvalue').val(FieldsIDs);
                }
                // Prevent click event from propagating to parent
                e.stopPropagation();
            });

            // Handle click on "Select all" control
            jQuery('th input[name="select_all"]').on('click', function (e) {
                if (this.checked) {
                    jQuery('#cmsfieldsTable tbody input[type="checkbox"]:not(:checked)').trigger('click');
                    var allFieldsIDs = [];
                    jQuery('.select_one').each(function (index, el) {
                        allFieldsIDs.push(jQuery(this).val());
                    });
                    jQuery('#fieldvalue').val(allFieldsIDs);
                } else {
                    jQuery('#cmsfieldsTable tbody input[type="checkbox"]:checked').trigger('click');
                    jQuery('#fieldvalue').val("");
                }
                // Prevent click event from propagating to parent
                e.stopPropagation();
            });

            jQuery('#deleteField').click(function (event) {
                var deleteId = jQuery('#fieldvalue').val();
                if (deleteId != '') {
                    jQuery('#deleteFieldsForm').submit();
                } else {
                    alert('Please select atleast one Item !');
                }
            });

            jQuery('.btn-micro').click(function (event) {
                var form = jQuery(this).parent('form');
                jQuery(form).submit();
            });

            jQuery('.store-group').click(function(){
                jQuery('#StoreGroup').submit();
            });
        });
        function updateGroupDetail(group_id)
        {
            var CSRF_TOKEN = '{{ csrf_token() }}';
            $.ajax({
                url : "{{url('/fields/getGroupDetail')}}",
                type : 'POST',
                async: false,
                data : {
                    _token   : CSRF_TOKEN,
                    group_id : group_id,
                },
                dataType : 'JSON',
                success : function(response) {

                    if (response.success)
                    {
                        $('#GroupModal').modal('show');
                        $("#group_id").val(response.data.id);
                        $("#display_name").val(response.data.display_name);
                        $("#active").val(response.data.active);
                        $("#task").val(response.data.task);

                    }
                }
            });
        }


    </script>
@endsection

