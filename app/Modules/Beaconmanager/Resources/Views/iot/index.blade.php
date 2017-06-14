@extends('beaconmanager::layouts.app')
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
    <div class="alert alert-success responseMessage" style="display:none"></div>
    <div class="container">
        <div class="row">
            <div class="panel panel-default">
                <div class="panel-heading">Iot Gateways</div>
                <div class="panel-body">
                    <div class="col-md-6">
                        <a class="btn btn-success" href="{{ route('iot.create') }}"> Create New Iot Gateway</a>
                        <input type="button" class="btn btn-danger" id="deleteMenu" value="Delete Iot Gateway">
                        {!! Form::open(['method' => 'DELETE','route' => ['iot.destroy'],'style'=>'display:inline', 'id' => 'deletePageForm']) !!}
                        <input type="hidden" name="catIDs" id="catVal" value="">
                        {!! Form::close() !!}

                    </div>

                    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered"
                           id="menuTable">
                        <thead>
                        <tr>
                            <th><input name="select_all" type="checkbox"></th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Device Id</th>
                            <th>Created Date</th>
                            <th>Updated Date</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
    jQuery(function($) {
            $('#menuTable').DataTable({
                processing : true,
                serverSide: true,
                bAutoWidth: true,
                bLengthChange:true,
                info:false,
                order: [[ 1, "asc" ]],
                ajax: '{!! url('beaconmanager/data-table-iot') !!}',
                columns: [
                    { data: 'check', name: '', width:10, orderable:false, searchable:false },
                    { data: 'title', name: 'title' },
                    { data: 'type', name: 'type' },
                    { data: 'device_id', name: 'device_id' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'updated_at', name: 'updated_at' }
                ],
                aoColumnDefs: [{
                    "aTargets": [ 1 ], // Column to target
                     "mRender": function ( data, type, full ) {
                         if (full.title != "")
                         {
                             returnStr =  '<a href="{{ url('beaconmanager/iot')}}/'+full.id+'/edit">'+full.title+'</a>';
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
                    }
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
                jQuery('#catVal').val(CmsPageIDs);
            } else {
                var pageID = jQuery(this).val();
                var removeIndex = jQuery.inArray(pageID, CmsPageIDs);
                CmsPageIDs.splice(removeIndex, 1);
                jQuery('#catVal').val(CmsPageIDs);
            }
            // Prevent click event from propagating to parent
            e.stopPropagation();
        });

        // Handle click on "Select all" control
        jQuery('th input[name="select_all"]').on('click', function (e) {
            if (this.checked) {
                jQuery('#menuTable tbody input[type="checkbox"]:not(:checked)').trigger('click');
                var allPageIDs = [];
                jQuery('.select_one').each(function (index, el) {
                    allPageIDs.push(jQuery(this).val());
                });
                jQuery('#catVal').val(allPageIDs);
            } else {
                jQuery('#menuTable tbody input[type="checkbox"]:checked').trigger('click');
                jQuery('#catVal').val("");
            }
            // Prevent click event from propagating to parent
            e.stopPropagation();
        });

        // FOR SEARCHING IN DATATABLE STARTS

        $('select.column_filter').on( 'change', function () {
            filterColumn( $(this).parents('div').attr('data-column') );
        } );

        function filterColumn ( i ) {
            $('#menuTable').DataTable().column( i ).search(
                    $('#col'+i+'_filter').val(),
                    $('#col'+i+'_regex').prop('checked'),
                    $('#col'+i+'_smart').prop('checked')
            ).draw();
        }

        // FOR SEARCHING IN DATATABLE ENDS
    });

    jQuery('#deleteMenu').click(function (event) {
        var deleteId = jQuery('#catVal').val();
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
</script>
@endsection

