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
                <strong>Edit User</strong>
            </div>
            <div class="panel-body">
                {!! Form::model($user, ['method' => 'PATCH','route' => ['users.update', $user->id]]) !!}
                <div class="box-body">
                    <div class="form-group">
                        <strong>Name:</strong>
                        {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                    </div>

                    <div class="form-group">
                        <strong>Email:</strong>
                        {!! Form::text('email', null, array('placeholder' => 'Email','class' => 'form-control')) !!}
                    </div>

                    <div class="form-group">
                        <strong>Password:</strong>
                        {!! Form::password('password', array('placeholder' => 'Password','class' => 'form-control')) !!}
                    </div>

                    <div class="form-group">
                        <strong>Confirm Password:</strong>
                        {!! Form::password('confirm-password', array('placeholder' => 'Confirm Password','class' => 'form-control')) !!}
                    </div>

                    <div class="form-group">
                        <strong>Role:</strong>
                        {!! Form::select('roles[]', $roles,$userRole, array('class' => 'form-control','multiple')) !!}
                    </div>
                    <div class="form-group">
                        <div class="pull-right">
                            <input type="submit" class="btn btn-primary" value="Submit">
                            <a href="{{ route('users.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
                        </div>
                    </div>
               </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
    <script>
        function getSelectOptions(id)
        {
            var subjectid = id;
            var courseid, token, url, data;
            token = $('input[name=_token]').val();
            courseid = $('#course').val();
            semesterid = $('#semesterid').val();
            url = '{{url('/attendance/get-subjects')}}';
            data = {courseid: courseid,semesterid: semesterid};
            $('#semester').empty();
            $('#subject').empty();
            $.ajax({
                url: url,
                headers: {'X-CSRF-TOKEN': token},
                data: data,
                type: 'POST',
                datatype: 'JSON',
                success: function (resp) {
                    $('#semester').html('');
                    $('#semester').append('<option value="">Select Semester</option>');
                    $('#subject').append('<option value="">Select Subject</option>');
                    $.each(resp, function (key, value){
                        if(parseInt(value.semester_id) == parseInt(semesterid))
                        {
                            $('#semester').append('<option selected value="'+value.semester_id+'">'+ value.semester_name +'</option>');
                        }
                        else
                        {
                            $('#semester').append('<option value="'+value.semester_id+'">'+ value.semester_name +'</option>');
                        }

                        if(parseInt(value.subject_id) == parseInt(subjectid))
                        {
                            $('#subject').append('<option selected value="'+value.subject_id+'">'+ value.subject_name +'</option>');
                        }
                        else
                        {
                            $('#subject').append('<option value="'+value.subject_id+'">'+ value.subject_name +'</option>');
                        }
                    });
                }
            });
        }
        $(document).on('change','#course, #semester',function(){
            getSelectOptions();
        });
        $(document).ready(function(){
            getSelectOptions();
        });
    </script>
@endsection