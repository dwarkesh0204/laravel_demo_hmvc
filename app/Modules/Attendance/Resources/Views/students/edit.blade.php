@extends('attendance::layouts.app')
@section('content')
<div class="container">
    <div class="row">
        @if (count($errors) > 0)
            <div class="alert alert-danger">
                <strong>Whoops!</strong> There were some problems with your input.<br><br>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>Edit Student</strong>
            </div>
            <div class="panel-body">
                {!! Form::model($data, ['method' => 'PATCH','route' => ['students.update', $data->id], 'files' => 'true'        ]) !!}
                <div class="box-body">
                    <div class="form-group">
                        {!! Form::label('name','Name:*') !!}<br />
                        {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('email','Email:*') !!}<br />
                        {!! Form::text('email', null, array('placeholder' => 'Email','class' => 'form-control')) !!}
                    </div>
                    <div class="form-group row">
                        {!! Form::label('avatar', 'Avatar:', ['class' => 'col-md-2 col-form-label']) !!}
                        <div class="col-md-3">
                            {!! Form::file('avatar') !!}
                        </div>
                        @if(!empty($data->avatar))
                            <div class="col-md-4">
                                {{Html::image(asset($data->avatar), $data->avatar, ['class' => 'cover-image', 'height' => '150px', 'width'=> '200px'])}}
                                <span class="glyphicon glyphicon-remove remove-cover" aria-hidden="false" style="color: red; cursor: pointer; margin-left: -20px; position: absolute; font-size: 20px;"></span>
                            </div>
                        @endif
                    </div>
                    <div class="form-group">
                        {!! Form::label('roll_no','Roll No.:*') !!}<br />
                        {!! Form::text('roll_no', null, array('placeholder' => 'Roll No.','class' => 'form-control')) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('courseid','Course:*') !!}<br />
                        {!! Form::select('courseid',(['' => 'Select Course'] + $courses),NULL,['name'=>'courseid','id'=>'courseid','class' => 'form-control','autocomplete'=>'off']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('semesterid','Semester:*') !!}<br />
                        {!! Form::select('semesterid',(['' => 'Select Semester']),NULL,['name'=>'semesterid','id'=>'semesterid','class' => 'form-control']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('phone_number','Phone No.:*') !!}<br />
                        {!! Form::text('phone_number', null, ['placeholder' => 'Phone number','class' => 'form-control']) !!}
                    </div>
                    <div class="col-lg-12">
                        <div class="pull-right">
                            <input type="submit" class="btn btn-primary" value="Submit">
                            <a href="{{ route('students.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="sectionModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete image.</p>
                {!! Form::open(['route' => ['teachers.deleteImage'],'id' => 'deleteImage']) !!}
                <input type="hidden" name="id" id="id" value="{{$data->id}}">
                {!! Form::close() !!}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default delete-image" data-dismiss="modal">Yes</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        jQuery('.remove-image').click(function(){
            jQuery('#sectionModal').modal('show');
        });

        jQuery('.delete-image').click(function(){
            jQuery('#deleteImage').submit();
        });
    });
</script>
<script>
    function getSemesters(id)
    {       var semesterid = id;
        var courseid, token, url, data;
        token = $('input[name=_token]').val();
        courseid = $('#courseid').val();
        url = '{{url('/attendance/get-semesters')}}';
        data = {courseid: courseid};
        $('#semesterid').empty();
        $.ajax({
            url: url,
            headers: {'X-CSRF-TOKEN': token},
            data: data,
            type: 'POST',
            datatype: 'JSON',
            success: function (resp) {
                $('#semesterid').html('');
                $('#semesterid').append('<option value="">Select Semester</option>');
                $.each(resp, function (key, value) {
                    if(parseInt(semesterid) == parseInt(key))
                    {
                        $('#semesterid').append('<option selected value="'+key+'">'+ value +'</option>');
                    }
                    else
                    {
                        $('#semesterid').append('<option value="'+key+'">'+ value +'</option>');
                    }
                });
            }
        });

    }
    $(document).ready(function(){
        getSemesters('{{$data->semesterid}}');
    });
    $(document).on('change','#courseid',function(){
        getSemesters();
    });
</script>
@endsection