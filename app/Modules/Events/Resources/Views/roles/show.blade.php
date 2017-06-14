@extends('events::layouts.app')
@section('content')
<div class="container">
    <div class="row">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>Show Role</strong>
                <span class="back pull-right"><a href="{{ route('roles.index') }}">Back</a></span>
            </div>
            <div class="panel-body">
                <div class="box-body">
                    <div class="form-group">
                        <strong>Name:</strong>
                        {{ $role->display_name }}
                    </div>
                    <div class="form-group">
                        <strong>Description:</strong>
                        {{ $role->description }}
                    </div>
                    <div class="form-group">
                        <strong>Permissions:</strong>
                        @if(!empty($rolePermissions))
                            @foreach($rolePermissions as $v)
                                <label class="label label-success">{{ $v->display_name }}</label>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
     </div>
</div>
@endsection