@extends('attendance::layouts.app')
@section('content')
    <div class="container">
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
                <strong>Subject</strong>
                <span class="back pull-right"></span>
            </div>
            <div class="panel-body">
                {!! Form::open(array('route' => 'subject.store','method'=>'POST')) !!}
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
                    <div class="col-lg-12">
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
    <script>
        $(document).on('change','#courseid',function(){
            var courseid, token, url, data;
            token = $('input[name=_token]').val();
            courseid = $(this).val();
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
                        $('#semesterid').append('<option value="'+key+'">'+ value +'</option>');
                    });
                }
            });
        });
    </script>
@endsection