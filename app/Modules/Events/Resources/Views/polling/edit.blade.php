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
                <strong>Session</strong>
                <span class="back pull-right"><a href="{{ route('pollquestion.index') }}">Back</a></span>
            </div>
            <div class="panel-body">
                {!! Form::model($polling_question, ['method' => 'PATCH','route' => ['pollquestion.update', $polling_question->id]]) !!}
                    <div class="box-body">
                        <div class="form-group">
                            {!! Form::label('session_id','Session:*') !!}<br />
                            {!! Form::select('session_id',(['0' => 'Select a Session'] + $sessions),$polling_question->session_id,['class' => 'form-control']) !!}
                        </div>
                        <div class="form-group">
                            {{Form::label('question', 'Question:')}}
                            {{Form::textarea('question',$polling_question->question,array('class' => 'form-control'))}}
                        </div>
                        @foreach($polling_question->poll_answers as $PollAnswer)
                            <div class="form-group">
                                {{Form::label('answer_'.$PollAnswer->answer_index, 'Answer '.$PollAnswer->answer_index.'*:')}}
                                {{Form::textarea('answer_'.$PollAnswer->answer_index,$PollAnswer->answer,array('class' => 'form-control'))}}
                            </div>
                        @endforeach
                        <div class="form-group">
                            {{Form::label('status', 'Active:')}}
                            <div class="form-inline">
                                <div class="radio">
                                    {{ Form::radio('status', '1', true)}}
                                    {{ Form::label('Yes', 'Yes')}}
                                </div>
                                <div class="radio">
                                    {{ Form::radio('status', '0', false)}}
                                    {{ Form::label('No', 'No') }}
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
