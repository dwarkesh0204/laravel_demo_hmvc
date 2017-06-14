@extends('events::layouts.app')
@section('content')
    <div class="container">
    <div class="row">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>Show User</strong>
                <span class="back pull-right"><a href="{{ route('users.index') }}">Back</a></span>
            </div>
            <div class="panel-body">
                <div class="box-body">
                    <div class="form-group">
                        <strong>Name:</strong>
                        {{ $user->name }}
                    </div>
                    <div class="form-group">
                        <strong>Email:</strong>
                        {{ $user->email }}
                    </div>
                    <div class="form-group">
                        <strong>Roles:</strong>
                        @if(!empty($user->roles))
                            @foreach($user->roles as $v)
                                <label class="label label-success">{{ $v->display_name }}</label>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection