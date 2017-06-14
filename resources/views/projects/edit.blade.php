@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>Project (Edit)</strong>
            </div>
            <div class="panel-body">
                {!! Form::model($item, ['files' => 'true', 'method' => 'PATCH','route' => ['projects.update', $item->id]]) !!}
                {{ Form::hidden('user_id', $user_id) }}
                    <div class="box-body">
                        <div class="form-group title">
                            {{Form::label('title', 'Title:*')}}
                            {{Form::text('title',null,array('class' => 'form-control', 'placeholder'=>'Title'))}}
                        </div>

                        <div class="box-body">
                            {{Form::label('type', 'Type:*')}}
                            {{ Form::select('type', ['0'=>'Select Type','ips'=>'Indoor Positioning','attendance'=>'Attendance System','events'=>'Events & Exhibitions','storesolution'=>'Store Solutions','visitors'=>'Visitors','proximity'=>'Proximity'],$item->type) }}
                        </div>

                        <div class="form-group">
                            {{Form::label('description', 'Description:')}}
                            {{Form::textarea('description',null,array('class' => 'form-control', 'rows' => '4', 'cols'=> '50'))}}
                        </div>

                        <div class="form-group col-lg-3">
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

                        <div class="form-group">
                            <div class="pull-right">
                                {{Form::submit('Submit',array('class' => 'btn btn-primary'))}}
                                <a href="{{ route('projects.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
                            </div>
                        </div>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#technig').summernote({
            height: 300,
        });
    })
</script>
@endsection
