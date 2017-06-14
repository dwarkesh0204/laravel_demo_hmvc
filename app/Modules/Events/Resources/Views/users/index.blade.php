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
                <div class="panel-heading">Users Management</div>
                <div class="panel-body">
                    <div class="col-md-6">
                        <a class="btn btn-success" href="{{ route('users.create') }}"> Create New User</a>
                    </div>
                    <div class="col-md-6">
                        <a class="btn btn-success" href="{{ route('users.import') }}"> Import New Users</a>
                    </div>
                    <div class="col-md-2" data-column="4">
                        <label>Roles</label>
                        <select class="selectmenulist col-sm-10 column_filter form-control input-sm" id="col4_filter" autocomplete="off">
                            <option value="">Select Roles</option>
                            @foreach ($roles as $key => $role)
                                <option value="{{ $role->display_name }}">{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered"
                           id="cmspageTable">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Contact No</th>
                            <!-- <th>Roles</th> -->
                            <th width="280px">Action</th>
                        </tr>
                        </thead>
                        <!-- @foreach ($data as $key => $user)
                            <tr>
                                <td>{{ ++$i }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone_number }}</td>
                                <td>
                                    @if(!empty($user->roles))
                                        @foreach($user->roles as $v)
                                            <label class="label label-success">{{ $v->display_name }}</label>
                                        @endforeach
                                    @endif
                                </td>
                                <td>
                                    <a class="btn btn-info" href="{{ route('users.show',$user->id) }}">Show</a>
                                    <a class="btn btn-primary" href="{{ route('users.edit',$user->id) }}">Edit</a>
                                    {!! \Form::open(['method' => 'DELETE','route' => ['users.destroy', $user->id],'style'=>'display:inline']) !!}
                                    {!! \Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
                                    {!! \Form::close() !!}
                                </td>
                            </tr>
                        @endforeach -->
                    </table>
                </div>
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
                ajax: '{!! url('events/data-table-users') !!}',
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'phone_number', name: 'phone_number' },
                    /*{ data: 'roles', name: 'roles' }*/
                ],
                aoColumnDefs: [{
                    "aTargets": [ 0 ], // Column to target
                     "mRender": function ( data, type, full ) {
                         if (full.name != "")
                         {
                             returnStr =  '<a href="{{ url('events/users')}}/'+full.id+'/edit">'+full.name+'</a>';
                            //returnStr =  '<span class="label label-default" title="'+full.ProjectType+'">'+full.ProjectType+'</span>';
                         }
                         else {
                            returnStr = '---';
                         }
                         return returnStr;
                     }
                    },
                    {
                        "aTargets": [ 3 ], // Column to target
                        "mRender": function ( data, type, full ) {
                            if (full.id != "")
                            {
                                returnStr =  '<a class="btn btn-info" href="{{ url('events/users')}}/'+full.id+'">Show</a><a class="btn btn-primary"href="{{ url('events/users')}}/'+full.id+'/edit">Edit</a>';
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

            // FOR SEARCHING IN DATATABLE STARTS

            $('select.column_filter').on( 'change', function () {
                filterColumn( $(this).parents('div').attr('data-column') );
            } );

            function filterColumn ( i ) {
                $('#cmspageTable').DataTable().column( i ).search(
                        $('#col'+i+'_filter').val(),
                        $('#col'+i+'_regex').prop('checked'),
                        $('#col'+i+'_smart').prop('checked')
                ).draw();
            }

            // FOR SEARCHING IN DATATABLE ENDS

        });
    </script>
@endsection