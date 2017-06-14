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
    @if(Session::has('delete_message'))
        <div class="alert alert-danger">
            {{ Session::get('delete_message') }}
        </div>
    @endif
    <div class="container">
        <div class="row">
            <div class="panel panel-default">
                <div class="panel-heading">User request for Event</div>
                <div class="panel-body">
                    <div class="col-md-6">
                        <input type="button" class="btn btn-danger" id="deletePage" value="Delete">
                        {!! Form::open(['method' => 'DELETE','route' => ['UserRequestForEvent.destroy',1 ],'style'=>'display:inline', 'id' => 'deletePageForm']) !!}
                        <input type="hidden" name="pageIDs" id="pagevalue" value="">
                        {!! Form::close() !!}
                    </div>
                    <div class="col-md-2" data-column="2">
                        <label>Event Filter</label>
                        <select class="selectmenulist col-sm-10 column_filter form-control input-sm" id="col2_filter" autocomplete="off">
                            <option value="">Select Event</option>
                            @foreach ($events as $key => $event)
                                <option value="{{ $event->name }}">{{ $event->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2" data-column="1">
                        <label>User Filter</label>
                        <select class="selectmenulist col-sm-10 column_filter form-control input-sm" id="col1_filter" autocomplete="off">
                            <option value="">Select User</option>
                            @foreach ($users as $key => $user)
                                <option value="{{ $user->name }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered"
                           id="cmspageTable">
                        <thead>
                        <tr>
                            <th><input name="select_all" type="checkbox"></th>
                            <th>Name</th>
                            <th>Event</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($userRequestForEvent as $user_request_detail)
                        <?php //dd($userRequestForEvent);die;?>
                            <tr>
                                <td><input type="checkbox" name="select_one" value="{{$user_request_detail['id']}}"
                                           class="select_one">
                                </td>
                                <td>{{$user_request_detail->userevent_user['name']}}</td>
                                <td>{{$user_request_detail->userevent_event['name']}}</td>
                                <td align="center">
                                    @if($user_request_detail->status == 1)
                                       {!! Form::open(['route' => ['UserRequestForEvent.changeStatus', $user_request_detail['id']],'style'=>'display:inline']) !!}
                                       <a class="btn btn-micro active" href="javascript:void(0);" onclick="" title=""
                                          data-original-title="Unpublish Item">
                                           <span class="glyphicon glyphicon-ok" aria-hidden="false"
                                                 style="color: green; font-size: 15px;"></span>
                                           <input type="hidden" name="mode" id="statusmode" value="unpublish">
                                       </a>
                                       {!! Form::close() !!}

                                    @else
                                       {!! Form::open(['route' => ['UserRequestForEvent.changeStatus', $user_request_detail['id']],'style'=>'display:inline']) !!}
                                       <a class="btn btn-micro active" href="javascript:void(0);" onclick="" title=""
                                          data-original-title="Publish Item">
                                           <span class="glyphicon glyphicon-remove" aria-hidden="false"
                                                 style="color: red; font-size: 15px;"></span>
                                           <input type="hidden" name="mode" id="statusmode" value="publish">
                                       </a>
                                       {!! Form::close() !!}
                                   @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            var CmsPageIDs = [];
            $('#cmspageTable').DataTable({
                select: true,
                "aoColumnDefs": [
                    {"bSortable": false, "aTargets": [0]},
                    { "width": "2%", "targets": 0 },
                    { "width": "8%", "targets": 3 },
                ],
            });
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

