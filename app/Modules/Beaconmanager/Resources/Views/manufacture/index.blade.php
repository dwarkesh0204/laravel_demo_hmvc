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
                <div class="panel-heading">Manufacturer</div>
                <div class="panel-body">
                    <div class="row">
                    <div class="col-md-6">

                        <a class="btn btn-success" href="{{ route('manufacture.create') }}"> Create New Manufacturer</a>
                        <input type="button" class="btn btn-danger" id="deleteMenu" value="Delete Manufacturer">
                        {!! Form::open(['method' => 'DELETE','route' => ['manufacture.destroy'],'style'=>'display:inline', 'id' => 'deletePageForm']) !!}
                        <input type="hidden" name="catIDs" id="catVal" value="">
                        {!! Form::close() !!}

                    </div>
                    </div>
                    <div class="row">
                    <div class="col-md-12">
                    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered"
                           id="menuTable">
                        <thead>
                        <tr>
                            <th><input name="select_all" type="checkbox"></th>
                            <th>Id</th>
                            <th>Title</th>
                            <th>Mac Series</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                    </table>
                    </div>
                    </div>
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
                order: [[ 2, "desc" ]],
                ajax: '{!! url('beaconmanager/data-table-manufacture') !!}',
                columns: [
                    { data: 'check', name: '', width:10, orderable:false, searchable:false },
                    { data: 'id', name: 'id',width:10, },
                    { data: 'title', name: 'title' },
                    { data: 'mac_series', name: 'mac_series' },
                    { data: 'status', name: 'status' }
                ],
                aoColumnDefs: [{
                    "aTargets": [ 2 ], // Column to target
                     "mRender": function ( data, type, full ) {
                         if (full.title != "")
                         {
                             returnStr =  '<a href="{{ url('beaconmanager/manufacture')}}/'+full.id+'/edit">'+full.title+'</a>';
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
                                returnStr =  '<input type="checkbox" name="select_one" value="'+full.id+'" class="select_one" autocomplete="off">';
                            }
                            else {
                                returnStr = '---';
                            }
                            return returnStr;
                        }
                    }
                    ,
                    {
                            "aTargets": [ 4 ], // Column to target
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

