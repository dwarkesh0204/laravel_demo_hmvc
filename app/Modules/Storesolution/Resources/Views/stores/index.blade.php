@extends('storesolution::layouts.app')
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
                <div class="panel-heading">Stores</div>
                <div class="panel-body">
                    <div class="col-md-6">
                        <a class="btn btn-success" href="{{ route('stores.create') }}"> Create Store</a>
                        <input type="button" class="btn btn-danger" id="deleteMenu" value="Delete Store">
                        {!! Form::open([ 'method'  => 'delete','id' => 'deletePageForm', 'route' => [ 'stores.destroy', 0] ]) !!}
                        <input type="hidden" name="IDs" id="Val" value="" autocomplete="off">
                        {!! Form::close() !!}
                    </div>
                    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered" id="cmspageTable">
                        <thead>
                        <tr>
                            <th><input name="select_all" type="checkbox" autocomplete="off"></th>
                            <th>Id</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Venue</th>
                            <th>Contact No</th>
                            <th>Email</th>
                            <th>Sections</th>
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
                ajax: '{!! url('storesolution/data-table-store') !!}',
                columns: [
                    { data: 'check', name: '', width:10, orderable:false, searchable:false },
                    { data: 'id', name: 'id',width:10, },
                    { data: 'name', name: 'name' },
                    { data: 'description', name: 'description' },
                    { data: 'venue', name: 'venue' },
                    { data: 'phone_no', name: 'phone_no' },
                    { data: 'email', name: 'email' },
                    { data: 'sections', name: 'sections' },
                    { data: 'status', name: 'status' }
                ],
                aoColumnDefs: [{
                    "aTargets": [ 2 ], // Column to target
                     "mRender": function ( data, type, full ) {
                         if (full.ProjectType != "")
                         {
                             returnStr =  '<a href="{{ url('storesolution/stores')}}/'+full.id+'/edit">'+full.name+'</a>';
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
                                returnStr =  '<td><input type="checkbox" name="select_one" value="'+full.id+'" class="select_one" autocomplete="off">';
                            }
                            else {
                                returnStr = '---';
                            }
                            return returnStr;
                        }
                    },
                    {
                        "aTargets": [ 8 ], // Column to target
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
                    },
                ],
            });
        });
        var CheckPageIDs = [];
        jQuery(document).on('click','td input[name="select_one"]', function (e) {
            if (this.checked) {
                var pageID = jQuery(this).val();
                CheckPageIDs.push(pageID);
                jQuery('#Val').val(CheckPageIDs);
            } else {
                var pageID = jQuery(this).val();
                var removeIndex = jQuery.inArray(pageID, CheckPageIDs);
                CheckPageIDs.splice(removeIndex, 1);
                jQuery('#Val').val(CheckPageIDs);
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
                jQuery('#Val').val(allPageIDs);
            } else {
                jQuery('#cmspageTable tbody input[type="checkbox"]:checked').trigger('click');
                jQuery('#Val').val("");
            }
            // Prevent click event from propagating to parent
            e.stopPropagation();
        });

        jQuery('#deleteMenu').click(function (event) {
            var deleteId = jQuery('#Val').val();
            if (deleteId != '') {
                jQuery('#deletePageForm').submit();
            } else {
                alert('Please select atleast one Item !');
            }
        });
    </script>
@endsection