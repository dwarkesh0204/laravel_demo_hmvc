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
                <div class="panel-heading">CMS Pages</div>
                <div class="panel-body">
                    <div class="col-md-6">
                        <a class="btn btn-success" href="{{ route('cmspages.create') }}"> Create New Page</a>

                        <input type="button" class="btn btn-danger" id="deletePage" value="Delete Page">
                        {!! Form::open(['method' => 'DELETE','route' => ['cmspages.destroy', 1],'style'=>'display:inline', 'id' => 'deletePageForm']) !!}
                        <input type="hidden" name="pageIDs" id="pagevalue" value="">
                        {!! Form::close() !!}
                    </div>

                    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered"
                           id="cmspageTable">
                        <thead>
                        <tr>
                            <th><input name="select_all" type="checkbox"></th>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <!-- <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td><input type="checkbox" name="select_one" value="{{$item->id}}" class="select_one">
                                </td>
                                <td><a href="{{ route('cmspages.edit',$item->id) }}">{{ $item->display_name }}</a></td>
                                <td align="center">
                                    @if($item->active == 1)
                                        {!! Form::open(['route' => ['cmspages.changeStatus', $item->id],'style'=>'display:inline']) !!}
                                        <a class="btn btn-micro active" href="javascript:void(0);" onclick="" title=""
                                           data-original-title="Unpublish Item">
                                            <span class="glyphicon glyphicon-ok" aria-hidden="false"
                                                  style="color: green; font-size: 15px;"></span>
                                            <input type="hidden" name="mode" id="statusmode" value="unpublish">
                                        </a>
                                        {!! Form::close() !!}

                                    @else
                                        {!! Form::open(['route' => ['cmspages.changeStatus', $item->id],'style'=>'display:inline']) !!}
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
                        </tbody> -->
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
                ajax: '{!! url('events/data-table-cmspages') !!}',
                columns: [
                    { data: 'check', name: '', width:10, orderable:false, searchable:false },
                    { data: 'id', name: 'id', },
                    { data: 'display_name', name: 'display_name' },
                    { data: 'active', name: 'active' },
                ],
                aoColumnDefs: [{
                    "aTargets": [ 2 ], // Column to target
                     "mRender": function ( data, type, full ) {
                         if (full.display_name != "")
                         {
                             returnStr =  '<a href="{{ url('events/cmspages')}}/'+full.id+'/edit">'+full.display_name+'</a>';
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
                    },
                    {
                        "aTargets": [ 3 ], // Column to target
                        "mRender": function ( data, type, full ) {
                            if (full.active == 1)
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
            /*$('#cmspageTable').DataTable({
                select: true,
                "aoColumnDefs": [
                    {"bSortable": false, "aTargets": [0]},
                    { "width": "2%", "targets": 0 },
                    { "width": "8%", "targets": 2 },
                ],
            });*/
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

