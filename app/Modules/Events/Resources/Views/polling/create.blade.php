@extends('events::layouts.app')
@section('content')
<div class="container">
    <div class="row">
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        @if(Session::has('flash_message'))
            <div class="alert alert-success">
                {{ Session::get('flash_message') }}
            </div>
        @endif

        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>Add Question</strong>
                <span class="back pull-right"><a href="{{ route('pollquestion.index') }}">Back</a></span>
            </div>
            <div class="panel-body">
                {!! Form::open(array('route' => 'pollquestion.store','method'=>'POST')) !!}
                    <div class="box-body">
                        <div class="form-group">
                            {!! Form::label('session_id','Session:*') !!}<br />
                            {!! Form::select('session_id',(['0' => 'Select a Session'] + $sessions),NULL,['class' => 'form-control']) !!}
                        </div>
                        <div class="form-group">
                            {{Form::label('question', 'Question:')}}
                            {{Form::textarea('question',null,array('class' => 'form-control', 'rows' => 3, 'cols' => 30))}}
                        </div>
                        <div class="form-group">
                            {{Form::label('answer_A', 'Answer A*:')}}
                            {{Form::textarea('answer_A',null,array('class' => 'form-control', 'rows' => 3, 'cols' => 30))}}
                        </div>
                        <div class="form-group">
                            {{Form::label('answer_B', 'Answer B*:')}}
                            {{Form::textarea('answer_B',null,array('class' => 'form-control', 'rows' => 3, 'cols' => 30))}}
                        </div>
                        <div class="form-group">
                            {{Form::label('answer_C', 'Answer C*:')}}
                            {{Form::textarea('answer_C',null,array('class' => 'form-control', 'rows' => 3, 'cols' => 30))}}
                        </div>
                        <div class="form-group">
                            {{Form::label('answer_D', 'Answer D*:')}}
                            {{Form::textarea('answer_D',null,array('class' => 'form-control', 'rows' => 3, 'cols' => 30))}}
                        </div>
                        <div class="form-group">
                            {{Form::label('status', 'Status:')}}
                            <div class="form-inline">
                                <div class="radio">
                                    {{ Form::radio('status', '1', true)}}
                                    {{ Form::label('status', 'Active')}}
                                </div>
                                <div class="radio">
                                    {{ Form::radio('status', '0', false)}}
                                    {{ Form::label('status', 'Inactive') }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            {{Form::submit('Save',array('class' => 'btn btn-primary btn-sm'))}}
                        </div>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection
