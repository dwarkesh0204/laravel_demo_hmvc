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
    @if ($message = Session::get('success'))

        <div class="alert alert-success">

            <p>{{ $message }}</p>

        </div>

    @endif
    <div class="container">
        <div class="row">
            <div class="panel panel-default">
                <div class="panel-heading">Role Management</div>
                <div class="panel-body">
                    <div class="col-md-6">
                        <a class="btn btn-success" href="{{ route('roles.create') }}"> Create New Role</a>
                    </div>
                    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered" id="cmspageTable">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th width="280px">Action</th>
                        </tr>
                        </thead>
                       <!--  @foreach ($roles as $key => $role)
                           <tr>
                               <td>{{ $role->display_name }}</td>
                               <td>{{ $role->description }}</td>
                               <td>
                                   <a class="btn btn-info" href="{{ route('roles.show',$role->id) }}">Show</a>
                                   @permission('role-edit')
                                   <a class="btn btn-primary" href="{{ route('roles.edit',$role->id) }}">Edit</a>
                                   @endpermission
                                   @permission('role-delete')
                                   {!! Form::open(['method' => 'DELETE','route' => ['roles.destroy', $role->id],'style'=>'display:inline']) !!}
                                   {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
                                   {!! Form::close() !!}
                                   @endpermission
                               </td>
                           </tr>
                       @endforeach -->
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
                order: [[ 0, "desc" ]],
                ajax: '{!! url('events/data-table-role') !!}',
                columns: [
                    { data: 'display_name', name: 'display_name' },
                    { data: 'description', name: 'description' },
                    { data: '', name: '' }
                ],
                aoColumnDefs: [{
                        "aTargets": [ 2 ], // Column to target
                        "mRender": function ( data, type, full ) {
                            if (full.display_name != "")
                            {
                                returnStr =  '<a class="btn btn-info" href="{{ route('roles.show',$role->id) }}">Show</a><a class="btn btn-primary" href="{{ route('roles.edit',$role->id) }}">Edit</a>';
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
            var EventsIDs = [];
           /* $('#cmspageTable').DataTable({
                select: true,
                "aoColumnDefs": [
                    {"bSortable": false, "aTargets": [0]},
                    { "width": "18%", "targets": 2 },
                ],
            });*/
            jQuery("th:first").removeClass('sorting_asc');

            jQuery('td input[name="select_one"]').on('click', function (e) {
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