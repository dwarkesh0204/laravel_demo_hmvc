@extends('ips::layouts.app')
@section('javascript')
    <style type="text/css">
        .dataTables_wrapper {
            margin-top: 50px;
        }
    </style>
@endsection
@section('content')
    <div class="container">
        <div class="row">
            <div class="panel panel-default">
                <div class="panel-heading">Floorplans</div>

                <div class="panel-body">
                    <div class="col-md-6">
                        <a class="btn btn-success" href="{{route('floorplan.create') }}">Create</a>
                        <input type="button" class="btn btn-danger" id="deleteFloorplan" value="Delete Floorplan">
                        {!! Form::open(['method' => 'DELETE','route' => ['floorplan.destroy'],'style'=>'display:inline', 'id' => 'deleteFloorplanForm']) !!}
                        <input type="hidden" name="floorPlanIDs" id="floorplanvalue" value="">
                        {!! Form::close() !!}
                    </div>
                    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered"
                           id="floorplanTable">
                        <thead>
                        <tr>
                            <th><input name="select_all" type="checkbox"></th>
                            <th>Name</th>
                            <th>Width (Feet)</th>
                            <th>Length (Feet)</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="myDeleteModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="modal-message">Are you sure you want to Delete Floorplan ?</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default delete-floorplan" data-dismiss="modal">Yes</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="blankModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="modal-message">Please select atleast one.</div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        var floorPlanIDs = [];
        jQuery(function($) {
            $('#floorplanTable').DataTable({
                processing : true,
                serverSide: true,
                bAutoWidth: true,
                bLengthChange:true,
                info:false,
                order: [[ 1, "desc" ]],
                ajax: '{!! url('ips/data-table-floorplan') !!}',
                columns: [
                    { data: 'check', name: 'select_one', width:10, orderable:false, searchable:false },
                    { data: 'name', name: 'name' },
                    { data: 'width', name: 'width' },
                    { data: 'height', name: 'height' }
                ],
                aoColumnDefs: [{
                    "aTargets": [ 1 ], // Column to target
                     "mRender": function ( data, type, full ) {
                         if (full.name != "")
                         {
                             returnStr =  '<a href="{{ url('ips/floorplan')}}/'+full.id+'/edit">'+full.name+'</a>';
                            //returnStr =  '<span class="label label-default" title="'+full.ProjectType+'">'+full.ProjectType+'</span>';
                         }
                         else {
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
            // Handle click on "Select all" control
            jQuery('th input[name="select_all"]').on('click', function (e) {
                if (this.checked) {
                    jQuery('#floorplanTable input[type="checkbox"]:not(:checked)').trigger('click');
                    var allFpIDs = [];
                    jQuery('.select_one').each(function (index, el) {
                        allFpIDs.push(jQuery(this).val());
                    });
                    jQuery('#floorplanvalue').val(allFpIDs);
                } else {
                    jQuery('#floorplanTable input[type="checkbox"]:checked').trigger('click');
                    jQuery('#floorplanvalue').val("");
                }
                // Prevent click event from propagating to parent
                e.stopPropagation();
            });

            jQuery('#deleteFloorplan').click(function (event) {
                if (jQuery('#floorplanvalue').val() == "") {
                    jQuery('#blankModal').modal('show');
                } else {
                    jQuery('#myDeleteModal').modal('show');
                }
            });

            jQuery('.delete-floorplan').click(function (event) {
                jQuery('#deleteFloorplanForm').submit();
            });
        });
        jQuery(document).on('click','td input[name="select_one"]', function (e) {
            if (this.checked) {
                var fpID = jQuery(this).val();
                floorPlanIDs.push(fpID);
                jQuery('#floorplanvalue').val(floorPlanIDs);
            } else {
                var fpID = jQuery(this).val();
                var removeIndex = jQuery.inArray(fpID, floorPlanIDs);
                floorPlanIDs.splice(removeIndex, 1);
                jQuery('#floorplanvalue').val(floorPlanIDs);
            }
            // Prevent click event from propagating to parent
            e.stopPropagation();
        });
    </script>
@endsection

