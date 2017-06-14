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
                <div class="panel-heading">Lectures</div>
                <div class="panel-body">

                    <div class="col-md-6">
                        <a class="btn btn-success" href="{{ route('lectures.create') }}"> Create Lecture</a>
                        <input type="button" class="btn btn-danger" id="deleteMenu" value="Delete Lecture">
                        {!! Form::open([ 'method'  => 'delete','id' => 'deletePageForm', 'route' => [ 'lectures.destroy', 0] ]) !!}
                        <input type="hidden" name="IDs" id="Val" value="" autocomplete="off">
                        {!! Form::close() !!}
                    </div>
                    <div class="col-md-3 pull-right" data-column="1">
                        <select autocomplete="off" class="selectmenulist col-sm-10 column_filter filter-status  form-control" id="col1_filter">
                            <option value="">Filter Course</option>
                            @foreach($courses as $course => $val)
                                <option value="{{$val}}">{{$val}}</option>
                            @endforeach
                        </select>
                    </div>

                    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered"
                           id="cmspageTable">
                        <thead>
                        <tr>
                            <th><input name="select_all" type="checkbox" autocomplete="off"></th>
                            <th>Course</th>
                            <th>Semester</th>
                            <th>Subject</th>
                            <th>Class</th>
                            <th>Start</th>
                            <th>End</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div id="confirm" class="modal hide fade">
        <div class="modal-body">
            Are you sure?
        </div>
        <div class="modal-footer">
            <button type="button" data-dismiss="modal" class="btn btn-primary" id="delete">Delete</button>
            <button type="button" data-dismiss="modal" class="btn">Cancel</button>
        </div>
    </div>
    <script type="text/javascript">
        jQuery(function($) {
           var table_obj =  $('#cmspageTable').DataTable({
                processing : true,
                serverSide: true,
                bAutoWidth: true,
                bLengthChange:true,
                info:false,
                order: [[ 5, "desc" ]],
                ajax: '{!! url('attendance/data-table-lectures') !!}',
                columns: [
                    { data: 'check', name: '', width:10, orderable:false, searchable:false },
                    { data: 'course', name: 'course.name' },
                    { data: 'semester', name: 'semester.name' },
                    { data: 'subject', name: 'subject.name' },
                    { data: 'class', name: 'class.name' },
                    { data: 'starttime', name: 'starttime' },
                    { data: 'endtime', name: 'endtime' }
                ],
                aaSorting: [],
                aoColumnDefs: [
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
                    }
                ],

            });
            $("select.column_filter").on('change', function () {
                table_obj.column( $(this).parents('div').attr('data-column'))
                        .search( this.value )
                        .draw();
            } );
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
            if (deleteId != '')
            {
                if (confirm("Are you sure! Attendance data for selected lectures will be deleted") == true)
                {
                    jQuery('#deletePageForm').submit();
                }
            } else {
                alert('Please select atleast one Item !');
            }
        });
    </script>

@endsection