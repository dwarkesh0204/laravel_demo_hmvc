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
    <div class="alert alert-success responseMessage" style="display:none"></div>
    <div class="container">
        <div class="row">
            <div class="panel panel-default">
                <div class="panel-heading">Categories</div>
                <div class="panel-body">
                    <div class="col-md-6">
                        <a class="btn btn-success" href="{{ route('category.create') }}"> Create New Category</a>

                        <input type="button" class="btn btn-danger" id="deleteMenu" value="Delete Category">
                        {!! Form::open(['method' => 'DELETE','route' => ['category.destroy', 1],'style'=>'display:inline', 'id' => 'deletePageForm']) !!}
                        <input type="hidden" name="catIDs" id="catVal" value="">
                        {!! Form::close() !!}
                    </div>

                    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered"
                           id="menuTable">
                        <thead>
                        <tr>
                            <th><input name="select_all" type="checkbox"></th>
                            <th>Id</th>
                            <th>Title</th>
                            <th>Created Date</th>
                            <th>Updated Date</th>
                            <th>Status</th>
                            <!-- <th data-orderable="false">Order <a id="storeOrder" href="javascript:void(0)" style="float: right"><i class="fa fa-floppy-o" aria-hidden="true"></i></a></th>
                            <th data-orderable="false" style="display: none;"></th> -->

                        </tr>
                        </thead>
                        <tbody>
                        @foreach($items as $key => $item)
                            <tr>
                                <td><input type="checkbox" name="select_one" value="{{$item['id']}}" class="select_one">
                                </td>
                                <td align="center">{{ $item['id'] }}</td>
                                <td><a href="{{ route('category.edit',$item['id']) }}">{{ $item['title'] }}</a></td>
                                <td>{{ $item['created_at'] }}</td>
                                <td>{{ $item['updated_at'] }}</td>
                                <td align="center">
                                    @if($item['status'] == 1)
                                        {!! Form::open(['route' => ['category.changeStatus', $item['id']],'style'=>'display:inline']) !!}
                                        <a class="btn btn-micro active" href="javascript:void(0);" onclick="" title=""
                                           data-original-title="Unpublish Item">
                                            <span class="glyphicon glyphicon-ok" aria-hidden="false"
                                                  style="color: green; font-size: 15px;"></span>
                                            <input type="hidden" name="mode" id="statusmode" value="0">
                                        </a>
                                        {!! Form::close() !!}

                                    @else
                                        {!! Form::open(['route' => ['category.changeStatus', $item['id']],'style'=>'display:inline']) !!}
                                        <a class="btn btn-micro active" href="javascript:void(0);" onclick="" title=""
                                           data-original-title="Publish Item">
                                            <span class="glyphicon glyphicon-remove" aria-hidden="false"
                                                  style="color: red; font-size: 15px;"></span>
                                            <input type="hidden" name="mode" id="statusmode" value="1">
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
        $('#menuTable').DataTable({
            processing : true,
            serverSide: true,
            bAutoWidth: true,
            bLengthChange:true,
            info:false,
            order: [[ 2, "desc" ]],
            ajax: '{!! url('events/data-table-Categories') !!}',
            columns: [
                { data: 'check', name: '', width:10, orderable:false, searchable:false },
                { data: 'id', name: 'id',width:10, },
                { data: 'title', name: 'title' },
                { data: 'created_at', name: 'created_at' },
                { data: 'updated_at', name: 'updated_at' },
                { data: 'status', name: 'status' }
            ],
            aoColumnDefs: [{
                "aTargets": [ 2 ], // Column to target
                 "mRender": function ( data, type, full ) {
                     if (full.title != "")
                     {
                         returnStr =  '<a href="{{ url('events/category')}}/'+full.id+'/edit">'+full.title+'</a>';
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

    jQuery(document).ready(function ($) {
        var CmsPageIDs = [];
        /*$('#menuTable').DataTable({
            select: true,
            "aoColumnDefs": [
                {"bSortable": false, "aTargets": [0]},
                { "width": "3%", "targets": 0 },
                { "width": "3%", "targets": 1 },
                { "width": "15%", "targets": 3 },
                { "width": "15%", "targets": 4 },
                { "width": "5%", "targets": 5}
            ],
        });*/

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

    /*jQuery('#storeOrder').click(function (event) {

        var TableData = '';
        var arr = [];
        $('#menuTable tr').each(function(row, tr)
        {
            var orderVal = $(tr).find('td:eq(3) input[type="text"]').val() + ':' + $(tr).find('td:eq(4)').text();
            arr.push(orderVal);

        });
        var orderData = arr.toString();
        var CSRF_TOKEN = '{{ csrf_token() }}';

        $.ajax({
            url : '{{url('/menu/saveOrder')}}',
            type : 'POST',
            data : {
                _token : CSRF_TOKEN,
                orderData:orderData,
            },
            dataType : 'JSON',
            success : function(response) {
                if (response.success) {
                    $('.responseMessage').css('display', 'block');
                    $('div.responseMessage').text('Order Saved sucessfully').delay(3200).fadeOut(300);
                    location.reload();
                } else {
                    alert('some Error');
                }
            }
        });
    });*/


    /*$('input').on('focusin', function(){
    console.log("Saving value " + $(this).val());
    $(this).data('val', $(this).val());
    });

    $('input').on('change', function(){
        var prev = $(this).data('val');
        var current = $(this).val();

        var columnId = $("input[value="+current+"]");


        console.log("Prev value " + prev);
        console.log("New value " + current);
        console.log("Column Id " + columnId);
    });*/





</script>
@endsection

