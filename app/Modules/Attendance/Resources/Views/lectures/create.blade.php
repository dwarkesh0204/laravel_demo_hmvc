@extends('attendance::layouts.app')
@section('content')
    <div class="container">
        @if ($message = Session::get('error'))
            <div class="alert alert-danger">
                <p>{{ $message }}</p>
            </div>
        @endif
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
                <strong>Lecture</strong>
                <span class="back pull-right"></span>
            </div>
            <div class="panel-body">
                {!! Form::open(array('route' => 'lectures.store','method'=>'POST')) !!}
                <div class="box-body">
                    <div class="form-group">
                        {!! Form::label('course','Course:*') !!}<br />
                        {!! Form::select('course',(['' => 'Select Course'] + $course),NULL,['name'=>'course','id'=>'course','class' => 'form-control','autocomplete'=>'off','onchange'=> 'getSemesterSelectOptions()']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('semester','Semester:*') !!}<br />
                        {!! Form::select('semester',(['' => 'Select Semester']),NULL,['name'=>'semester','id'=>'semester','class' => 'form-control','onchange'=> 'getSubjectSelectOptions()']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('subject','Subject:*') !!}<br />
                        {!! Form::select('subject',(['' => 'Select Subject']),NULL,['name'=>'subject','id'=>'subject','class' => 'form-control']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('class','Class:*') !!}<br />
                        {!! Form::select('class',(['' => 'Select Class'] + $class),NULL,['name'=>'class','id'=>'class','class' => 'form-control']) !!}
                    </div>
                    <div class="form-group row">
                        {{Form::label('startdate', 'Start Date*:', ['class' => 'col-md-2 col-form-label'])}}
                        <div class="col-md-3">
                            {{Form::text('startdate', null, ['class' => 'form-control', 'id' => 'startdate'])}}
                        </div>
                        <div class="col-md-2">
                            {{Form::text('starttime', null, ['class' => 'form-control', 'id' => 'starttime'])}}
                        </div>
                    </div>

                    <div class="form-group row">
                        {{Form::label('enddate', 'End Date*:', ['class' => 'col-md-2 col-form-label'])}}
                        <div class="col-md-3">
                            {{Form::text('enddate', null, ['class' => 'form-control', 'id' => 'enddate'])}}
                        </div>
                        <div class="col-md-2">
                            {{Form::text('endtime', null, ['class' => 'form-control', 'id' => 'endtime'])}}
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="pull-right">
                            <input type="submit" class="btn btn-primary" value="Submit">
                            <a href="{{ route('lectures.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $("#startdate").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: "yy-mm-dd",
                minDate: 0,
            });

            $("#enddate").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: "yy-mm-dd",
                minDate: 0,
            });

            $('#starttime').timepicker({
                timeFormat: 'HH:mm',
                interval: 30,
                scrollbar: true
            });

            $('#endtime').timepicker({
                timeFormat: 'HH:mm',
                interval: 30,
                scrollbar: true
            });
        });
        function getSemesterSelectOptions(id)
        {
            var courseid, token, url, data,semesterid,type;
            token = $('input[name=_token]').val();
            courseid = $('#course').val();
            type = 'semesters';
            url = '{{url('/attendance/get-subjects')}}';
            data = {courseid: courseid,type:type};
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
                    $.each(resp, function (key, value){
                        if(parseInt(value.semester_id) == parseInt(semesterid))
                        {
                            $('#semester').append('<option selected value="'+value.semester_id+'">'+ value.semester_name +'</option>');
                        }
                        else
                        {
                            $('#semester').append('<option value="'+value.semester_id+'">'+ value.semester_name +'</option>');
                        }
                    });
                }
            });
        }
        function getSubjectSelectOptions(id)
        {
            var subjectid = id;
            var courseid, token, url, data,type,semesterid;
            type = 'subjects';
            token = $('input[name=_token]').val();
            courseid = $('#course').val();
            semesterid = $('#semester').val();
            url = '{{url('/attendance/get-subjects')}}';
            data = {courseid: courseid,semesterid: semesterid,type:type};
            $('#subject').empty();
            $.ajax({
                url: url,
                headers: {'X-CSRF-TOKEN': token},
                data: data,
                type: 'POST',
                datatype: 'JSON',
                success: function (resp) {
                    $('#subject').append('<option value="">Select Subject</option>');
                    $.each(resp, function (key, value){
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
        /*
        $(document).on('change','#course, #semester',function(){
            getSelectOptions();
        });*/
    </script>
@endsection