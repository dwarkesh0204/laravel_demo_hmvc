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
                <strong>Edit Subject</strong>
            </div>
            <div class="panel-body">
                {!! Form::model($data, ['method' => 'PATCH','route' => ['subject.update', $data->id]]) !!}
                <div class="box-body">
                    <div class="form-group">
                        {!! Form::label('courseid','Course:*') !!}<br />
                        {!! Form::select('courseid',(['' => 'Select Course'] + $courses),NULL,['name'=>'courseid','id'=>'courseid','class' => 'form-control','autocomplete'=>'off']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('semesterid','Semester:*') !!}<br />
                        {!! Form::select('semesterid',(['' => 'Select Semester']),NULL,['name'=>'semesterid','id'=>'semesterid','class' => 'form-control']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('name','Name:*') !!}<br />
                        {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('description','Description:*') !!}<br />
                        {!! Form::textarea('description', null, ['placeholder' => 'Description','class' => 'form-control']) !!}
                    </div>
                    <div class="form-group">
                        <div class="pull-right">
                            <input type="submit" class="btn btn-primary" value="Submit">
                            <a href="{{ route('subject.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
                        </div>
                    </div>
               </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
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