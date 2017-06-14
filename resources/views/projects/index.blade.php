@extends('layouts.app')
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
                <div class="panel-heading">Projects</div>
                <div class="panel-body">
                    {{--<div class="col-md-6">
                        <a class="btn btn-success" href="{{ route('projects.create') }}"> Create New Project</a>
                        <input type="button" class="btn btn-danger" id="deleteMenu" value="Delete Project">
                        {!! Form::open(['method' => 'DELETE','route' => ['projects.destroy'],'style'=>'display:inline', 'id' => 'deletePageForm']) !!}
                        <input type="hidden" name="catIDs" id="catVal" value="">
                        {!! Form::close() !!}
                    </div>--}}
                    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered"
                           id="menuTable">
                        <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Secret</th>
                            <th>Created Date</th>
                            <th>Updated Date</th>
                            <th>Status</th>
                            {{--<th>Access</th>--}}
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($items as $key => $item)
                            <tr>
                                {{--<td><a href="{{ route('projects.edit',$item['id']) }}">{{ $item['title'] }}</a></td>--}}
                                <td>{{ $item['title'] }}</td>
                                <td>{{ $item['type'] }}</td>
                                <td><b>{{ $item['secret'] }}</b></td>
                                <td>{{ $item['created_at'] }}</td>
                                <td>{{ $item['updated_at'] }}</td>
                                <td align="center">
                                    @if($item['status'] == 1)
                                        <span class="glyphicon glyphicon-ok" aria-hidden="false" style="color: green; font-size: 15px;"></span>
                                    @else
                                        <span class="glyphicon glyphicon-remove" aria-hidden="false" style="color: red; font-size: 15px;"></span>
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
        $('#menuTable').DataTable({
            select: true,
            "aoColumnDefs": [
                {"bSortable": false, "aTargets": [0,8]},
                { "width": "3%", "targets": 0 },
            ],
        });

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
<script type="text/javascript">
    jQuery(function($) {
        $('#cmspageTable').DataTable({
            processing : true,
            serverSide: true,
            bAutoWidth: true,
            bLengthChange:true,
            info:false,
            order: [[ 5, "desc" ]],
            ajax: '{!! url('data-table-projects') !!}',
            columns: [
                { data: 'id', name: 'id' },
                { data: 'course', name: 'course' },
                { data: 'semester', name: 'semester' },
                { data: 'subject', name: 'subject' },
                { data: 'class', name: 'class' },
                { data: 'starttime', name: 'starttime' },
                { data: 'endtime', name: 'endtime' }
            ],
            aaSorting: [],
            aoColumnDefs: [
                {
                    "aTargets": [ 0 ], // Column to target
                    "bSearchable": false,
                    "mRender": function ( data, type, full ) {
                        /*returnStr =  '<a href="{{url('/lectures')}}/'+full.id+'/edit"  title="'+full.id+'">'+full.id+'</a>';*/
                        returnStr =  full.id;
                        return returnStr;
                    }
                }
            ],
        });
    });
</script>
@endsection

