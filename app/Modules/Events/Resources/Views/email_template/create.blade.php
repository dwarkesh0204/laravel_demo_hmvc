@extends('events::layouts.app')
@section('javascript')
<script type="text/javascript">
</script>
@endsection
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
                <strong>Email Template</strong>
            </div>
            <div class="panel-body">
                {!! Form::open(array('route' => 'email_template.store','method'=>'POST','files'=>'true')) !!}


                    <div class="box-body">
                        <div class="form-group row">
                            <div class="col-lg-6">
                                <div class="form-group title">
                                    {{Form::label('title', 'Title:*')}}
                                    {{Form::text('title',null,array('class' => 'form-control', 'placeholder'=>'Title'))}}
                                </div>

                                <div class="form-group title">
                                    {{Form::label('subject', 'Subject:*')}}
                                    {{Form::text('subject',null,array('class' => 'form-control', 'placeholder'=>'Subject'))}}
                                </div>

                                <div class="form-group">
                                    {{Form::label('message', 'Message*:')}}
                                    {{Form::textarea('message',null,array('class' => 'form-control', 'rows' => '4', 'cols'=> '50'))}}
                                </div>

                                <div class="form-group">
                                    {{Form::label('type', 'Type:')}}
                                    {{Form::hidden('type', 'register', ['id' => 'type'])}}
                                    {{Form::label('type', 'Register')}}
                                </div>

                                <div class="form-group">
                                    {{Form::label('active', 'Active:')}}
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
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group title">
                                    {{Form::label('from', 'From:*')}}
                                    {{Form::text('from',null,array('class' => 'form-control', 'placeholder'=>'From'))}}
                                </div>

                                <div class="form-group title">
                                    {{Form::label('from_name', 'From Name:*')}}
                                    {{Form::text('from_name',null,array('class' => 'form-control', 'placeholder'=>'From Name'))}}
                                </div>

                                <div class="form-group title">
                                    {{Form::label('reply_to', 'Reply To:')}}
                                    {{Form::text('reply_to',null,array('class' => 'form-control', 'placeholder'=>'Reply To'))}}
                                </div>

                                <div class="form-group title">
                                    {{Form::label('reply_to_name', 'Reply To Name:')}}
                                    {{Form::text('reply_to_name',null,array('class' => 'form-control', 'placeholder'=>'Reply to Name'))}}
                                </div>

                                <div class="form-group">
                                    {{Form::label('tags', 'Tags:')}}
                                    {{Form::hidden('tags', '{first_name},{user_email},{event_name},{role_name},{user_password},{link_ios},{link_android}', ['id' => 'tags'])}}
                                    Tags Allowed for Email Message (body) as follow <br />

                                    1) {first_name} <br />
                                    2) {user_email} <br />
                                    3) {event_name} <br />
                                    4) {role_name}  <br />
                                    5) {user_password}  <br />
                                    6) {link_ios}   <br />
                                    7) {link_android}  <br />
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="pull-right">
                                    {{Form::hidden('created_by', \Auth::user()->id, ['id' => 'type'])}}
                                    {{Form::submit('Submit',array('class' => 'btn btn-primary'))}}
                                    <a href="{{ route('email_template.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
                                </div>
                            </div>
                        </div>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection
