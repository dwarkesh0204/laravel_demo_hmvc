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
                <div class="panel-heading">Events</div>
                <div class="panel-body">
                    <div class="col-md-6">

                        <a class="btn btn-success" href="{{ route('events.create') }}"> Create New Event</a>
                        <input type="button" class="btn btn-danger" id="deletePage" value="Delete Event">
                        {!! Form::open(['method' => 'DELETE','route' => ['events.destroy', 0],'style'=>'display:inline', 'id' => 'deletePageForm']) !!}
                        <input type="hidden" name="pageIDs" id="pagevalue" value="">
                        {!! Form::close() !!}

                    </div>

                    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered"
                           id="cmspageTable">
                        <thead>
                        <tr>
                            <th><input name="select_all" type="checkbox"></th>
                            <th>Title</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Venue</th>
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
                ajax: '{!! url('events/data-table-events') !!}',
                columns: [
                    { data: 'check', name: '', width:10, orderable:false, searchable:false },
                    { data: 'name', name: 'name' },
                    { data: 'startdate', name: 'startdate' },
                    { data: 'enddate', name: 'enddate' },
                    { data: 'venue', name: 'venue' },
                    { data: 'status', name: 'status' },
                ],
                aoColumnDefs: [{
                    "aTargets": [ 1 ], // Column to target
                     "mRender": function ( data, type, full ) {
                         if (full.name != "")
                         {
                             returnStr =  '<a href="{{ url('events/events')}}/'+full.eventid+'/edit">'+full.name+'</a>';
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
                            if (full.eventid != "")
                            {
                                returnStr =  '<td><input type="checkbox" name="select_one" value="'+full.eventid+'" class="select_one" autocomplete="off">';
                            }
                            else {
                                returnStr = '---';
                            }
                            return returnStr;
                        }
                    },
                    {
                        "aTargets": [ 5 ], // Column to target
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
            var EventsIDs = [];
            jQuery("th:first").removeClass('sorting_asc');

            jQuery(document).on('click','.select_one', function (e) {
                if (this.checked) {
                    var EventID = jQuery(this).val();
                    EventsIDs.push(EventID);
                    jQuery('#pagevalue').val(EventsIDs);
                } else {
                    var EventID = jQuery(this).val();
                    var removeIndex = jQuery.inArray(EventID, EventsIDs);
                    EventIDs.splice(removeIndex, 1);
                    jQuery('#pagevalue').val(EventsIDs);
                }
                // Prevent click event from propagating to parent
                e.stopPropagation();
            });

            // Handle click on "Select all" control
            jQuery('th input[name="select_all"]').on('click', function (e) {
                if (this.checked) {
                    jQuery('#cmspageTable tbody input[type="checkbox"]:not(:checked)').trigger('click');
                    var allEventsIDs = [];
                    jQuery('.select_one').each(function (index, el) {
                        allEventsIDs.push(jQuery(this).val());
                    });
                    jQuery('#pagevalue').val(allEventsIDs);
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