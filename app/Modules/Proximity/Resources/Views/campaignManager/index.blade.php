@extends('proximity::layouts.app')
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
                <div class="panel-heading">Campaign Manager</div>
                <div class="panel-body">
                    <div class="col-md-6">

                        <a class="btn btn-success" href="{{ route('campaignManager.create') }}"> Create</a>



                        <input type="button" class="btn btn-danger" id="deletePage" value="Delete">
                        {!! Form::open(['method' => 'DELETE','route' => ['campaignManager.destroy', 0],'style'=>'display:inline', 'id' => 'deletePageForm']) !!}
                        <input type="hidden" name="campaignManagerIDs" id="pagevalue" value="">
                        {!! Form::close() !!}

                    </div>

                    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered"
                           id="cmspageTable">
                        <thead>
                        <tr>
                            <th><input name="select_all" type="checkbox"></th>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Created Date</th>
                            <th>Updated Date</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        jQuery(function($) {
            $('#cmspageTable').DataTable({
                processing : true,
                serverSide: true,
                bAutoWidth: true,
                bLengthChange:true,
                info:false,
                order: [[ 2, "desc" ]],
                ajax: '{!! url('proximity/data-table-campaignManager') !!}',
                columns: [
                    { data: 'check', name: '', width:10, orderable:false, searchable:false },
                    { data: 'id', name: 'id', },
                    { data: 'title', name: 'title' },
                    { data: 'description', name: 'description' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'updated_at', name: 'updated_at' },
                    { data: 'status', name: 'status' }
                ],
                aoColumnDefs: [{
                    "aTargets": [ 2 ], // Column to target
                    "mRender": function ( data, type, full ) {
                        if (full.title != "")
                        {
                             returnStr =  '<a href="{{ url('proximity/campaignManager')}}/'+full.id+'/edit">'+full.title+'</a>';
                        }
                        else
                        {
                           returnStr = '---';
                        }
                        return returnStr;
                     }
                    },
                    {
                        "aTargets": [ 0 ], // Column to target
                        "mRender": function ( data, type, full ) {
                            if (full.id != "")
                            {
                                returnStr =  '<td><input type="checkbox" name="select_one" value="'+full.id+'" class="select_one" autocomplete="off">';
                            }
                            else {
                                returnStr = '---';
                            }
                            return returnStr;
                        }
                    },
                    {
                        "aTargets": [ 6 ], // Column to target
                        "mRender": function ( data, type, full ) {
                            if (full.status == 1)
                            {
                                returnStr =  '<span class="glyphicon glyphicon-ok" aria-hidden="false" style="color: green; font-size: 15px;"></span>';
                            }
                            else
                            {
                                returnStr =  '<span class="glyphicon glyphicon-remove" aria-hidden="false" style="color: red; font-size: 15px;"></span>';
                            }
                        return returnStr;
                        }
                    },
                ],
            });
        });

        jQuery(document).ready(function ($) {
            var CmsPageIDs = [];
            jQuery("th:first").removeClass('sorting_asc');

            jQuery('td input[name="select_one"]').on('click', function (e) {
                if (this.checked) {
                    var pageID = jQuery(this).val();
                    CmsPageIDs.push(pageID);
                    jQuery('#pagevalue').val(CmsPageIDs);
                } else {
                    var pageID = jQuery(this).val();
                    var removeIndex = jQuery.inArray(pageID, CmsPageIDs);
                    CmsPageIDs.splice(removeIndex, 1);
                    jQuery('#pagevalue').val(CmsPageIDs);
                }
                // Prevent click event from propagating to parent
                e.stopPropagation();
            });

            // Handle click on "Select all" control
            jQuery('th input[name="select_all"]').on('click', function (e) {
                if (this.checked) {
                    jQuery('#cmspageTable tbody input[type="checkbox"]:not(:checked)').trigger('click');
                    var allPageIDs = [];
                    jQuery('.select_one').each(function (index, el) {
                        allPageIDs.push(jQuery(this).val());
                    });
                    jQuery('#pagevalue').val(allPageIDs);
                } else {
                    jQuery('#cmspageTable tbody input[type="checkbox"]:checked').trigger('click');
                    jQuery('#pagevalue').val("");
                }
                // Prevent click event from propagating to parent
                e.stopPropagation();
            });

            jQuery('#deletePage').click(function (event) {
                var deleteId = jQuery('#pagevalue').val();
                if (deleteId != '') {
                    jQuery('#deletePageForm').submit();
                } else {
                    alert('Please select atleast one Item !');
                }
            });

            jQuery('.btn-micro').click(function (event) {
                var form = jQuery(this).parent('form');
                jQuery(form).submit();
            });

        });
    </script>
@endsection

