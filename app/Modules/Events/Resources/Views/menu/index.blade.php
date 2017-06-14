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
                <div class="panel-heading">Menu Management</div>
                <div class="panel-body">
                    <div class="col-md-6">
                        <a class="btn btn-success" href="{{ route('menu.create') }}"> Create New Menu</a>

                        <input type="button" class="btn btn-danger" id="deleteMenu" value="Delete Menu">
                        {!! Form::open(['method' => 'DELETE','route' => ['menu.destroy'],'style'=>'display:inline', 'id' => 'deletePageForm']) !!}
                        <input type="hidden" name="MenuIDs" id="menuvalue" value="">
                        {!! Form::close() !!}
                    </div>

                    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered"
                           id="menuTable">
                        <thead>
                        <tr>
                            <th><input name="select_all" type="checkbox"></th>
                            <th>No</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th data-orderable="false">Order <a id="storeOrder" href="javascript:void(0)" style="float: right"><i class="fa fa-floppy-o" aria-hidden="true"></i></a></th>
                            <th data-orderable="false" style="display: none;"></th>

                        </tr>
                        </thead>
                    </table>
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
            ajax: '{!! url('events/data-table-menu') !!}',
            columns: [
                { data: 'check', name: '', width:10, orderable:false, searchable:false },
                { data: 'id', name: 'id',width:10, },
                { data: 'title', name: 'title' },
                { data: 'status', name: 'status' },
                { data: 'order', name: 'order' }
            ],
            aoColumnDefs: [{
                "aTargets": [ 2 ], // Column to target
                 "mRender": function ( data, type, full ) {
                     if (full.title != "")
                     {
                         returnStr =  '<a href="{{ url('events/menu')}}/'+full.id+'/edit">'+full.title+'</a>';
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
                    "aTargets": [ 3 ], // Column to target
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

        // Handle click on "Select all" control
        jQuery(document).on('click','th input[name="select_all"]', function (e) {
            if (this.checked) {
                jQuery('#menuTable tbody input[type="checkbox"]:not(:checked)').trigger('click');
                var allPageIDs = [];
                jQuery('.select_one').each(function (index, el) {
                    allPageIDs.push(jQuery(this).val());
                });
                jQuery('#menuvalue').val(allPageIDs);
            } else {
                jQuery('#menuTable tbody input[type="checkbox"]:checked').trigger('click');
                jQuery('#menuvalue').val("");
            }
            // Prevent click event from propagating to parent
            e.stopPropagation();
        });

        jQuery(document).on('click','.select_one', function (e) {
            if (this.checked) {
                var pageID = jQuery(this).val();
                CmsPageIDs.push(pageID);
                jQuery('#menuvalue').val(CmsPageIDs);
            } else {
                var pageID = jQuery(this).val();
                var removeIndex = jQuery.inArray(pageID, CmsPageIDs);
                CmsPageIDs.splice(removeIndex, 1);
                jQuery('#menuvalue').val(CmsPageIDs);
            }
            // Prevent click event from propagating to parent
            e.stopPropagation();
        });
    });

    jQuery('#deleteMenu').click(function (event) {
        var deleteId = jQuery('#menuvalue').val();
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

    jQuery('#storeOrder').click(function (event) {

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
            url : '{{url('events/menu/saveOrder')}}',
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
    });






   /* $('input').on('focusin', function(){
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

