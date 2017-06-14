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
                <div class="panel-heading">Sessions</div>
                <div class="panel-body">
                    <div class="col-md-6">
                        <a class="btn btn-success" href="{{ route('sessions.create') }}"> Create Session</a>

                        <input type="button" class="btn btn-danger" id="deletePage" value="Delete Session">
                        {!! Form::open(['method' => 'DELETE','route' => ['sessions.destroy', 1],'style'=>'display:inline', 'id' => 'deletePageForm']) !!}
                        <input type="hidden" name="pageIDs" id="pagevalue" value="">
                        {!! Form::close() !!}
                    </div>

                    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered"
                           id="cmspageTable">
                        <thead>
                        <tr>
                            <th><input name="select_all" type="checkbox"></th>
                            <th>Title</th>
                            <th>Speaker</th>
                            <th>Event</th>
                            <th>Venue</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($sessions as $session)
                            <tr>
                                <td><input type="checkbox" name="select_one" value="{{$session->id}}"
                                           class="select_one"></td>
                                <td><a href="{{ route('sessions.edit',$session->id) }}">{{ $session->name }}</a></td>
                                <td>
                                    <?php
                                        $speakers = $session->session_sessions_speakers->pluck('sessions_speakers_users')->toArray();
                                        $count_speaker = count($speakers);
                                    ?>
                                        @for ($i = 0; $i < $count_speaker; $i++)
                                            <?php $speaker = $speakers[$i];?>
                                            @if(isset($speaker) && !empty($speaker))
                                            {{$speaker['name']}}@if($count_speaker != $i+1), @endif
                                            @endif
                                        @endfor


                                </td>
                                <td><a href="{{ route('events.edit',$session->event_id) }}">{{ $session->events->name }}</a>
                                </td>
                                <td>{{ $session->venue }}</td>
                                <td>{{ $session->date }}</td>
                                <td>{{ $session->start_time }} - {{ $session->end_time }}</td>
                                <td align="center">
                                    @if($session->status == 1)
                                        {!! Form::open(['route' => ['sessions.changeStatus', $session->id],'style'=>'display:inline']) !!}
                                        <a class="btn btn-micro active" href="javascript:void(0);" onclick="" title=""
                                           data-original-title="Unpublish Item">
                                            <span class="glyphicon glyphicon-ok" aria-hidden="false"
                                                  style="color: green; font-size: 15px;"></span>
                                            <input type="hidden" name="mode" id="statusmode" value="unpublish">
                                        </a>
                                        {!! Form::close() !!}

                                    @else
                                        {!! Form::open(['route' => ['sessions.changeStatus', $session->id],'style'=>'display:inline']) !!}
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
                initComplete: function () {
                    this.api().columns().every( function () {
                        var column = this;
                        var select = $('<select><option value=""></option></select>')
                                .appendTo( $(column.footer()).empty() )
                                .on( 'change', function () {
                                    var val = $.fn.dataTable.util.escapeRegex(
                                            $(this).val()
                                    );

                                    column
                                            .search( val ? '^'+val+'$' : '', true, false )
                                            .draw();
                                } );

                        column.data().unique().sort().each( function ( d, j ) {
                            select.append( '<option value="'+d+'">'+d+'</option>' )
                        } );
                    } );
                },
                select: true,
                "aoColumnDefs": [
                    {"bSortable": false, "aTargets": [0]},
                    { "width": "2%", "targets": 0 },
                    { "width": "15%", "targets": 2 },
                    { "width": "14%", "targets": 6 },
                    { "width": "8%", "targets": 7 }
                ]
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

        });
    </script>
@endsection

