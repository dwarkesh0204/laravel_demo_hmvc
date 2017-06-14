@extends('attendance::layouts.app')
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
                <div class="panel-heading">Notifications</div>
                <div class="panel-body">
                    <div class="col-md-6">
                        <a class="btn btn-success" href="{{ route('notifications.create') }}"> Create Notification</a>
                    </div>
                    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered"
                           id="cmspageTable">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>Subject</th>
                            <th>Notification</th>
                            <th>Sent On</th>
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
                order: [[ 1, "desc" ]],
                ajax: '{!! url('attendance/data-table-notifications') !!}',
                columns: [
                    { data: 'id', name: 'id',width:10, },
                    { data: 'subject', name: 'subject' },
                    { data: 'notification', name: 'notification' },
                    { data: 'created_at', name: 'created_at' }
                ],
                aoColumnDefs: [{
                    "aTargets": [ 2 ], // Column to target
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

        jQuery('.btn-micro').click(function (event) {
            var form = jQuery(this).parent('form');
            jQuery(form).submit();
        });
    </script>
@endsection