@extends('events::layouts.app')

@section('javascript')

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
                <strong>CMS Page</strong>
            </div>
            <div class="panel-body">
                {!! Form::open(array('route' => 'cmspages.store','method'=>'POST')) !!}
                    <div class="box-body">
                        <div class="form-group title">
                            {{Form::label('display_name', 'Title:*')}}
                            {{Form::text('display_name',null,array('class' => 'form-control', 'placeholder'=>'Title'))}}
                        </div>

                        <div class="form-group slug" style="display: none;">
                            {{Form::label('name', 'Page Name:')}}
                            {{Form::text('name',null,array('class' => 'form-control'))}}
                        </div>

                        <div class="form-group">
                            {{Form::label('description', 'Content:')}}
                            {{Form::textarea('description',null,array('class' => 'form-control','id' => 'technig'))}}
                        </div>

                        <div class="form-group">
                            {!! Form::label('cat_id', 'Categories:', ['class' => 'control-label']) !!}
                            {!! Form::select('cat_id', (['' => '- Select Category -'] + $Categories), null, ['class' => 'form-control cat_id','id' => 'cat_id']) !!}
                        </div>

                        <div class="form-group">
                            {{Form::label('active', 'Active:')}}
                            <div class="form-inline">
                                <div class="radio">
                                    {{ Form::radio('active', '1', true)}}
                                    {{ Form::label('Yes', 'Yes')}}
                                </div>
                                <div class="radio">
                                    {{ Form::radio('active', '0', false)}}
                                    {{ Form::label('No', 'No') }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="pull-right">
                                {{Form::submit('Submit',array('class' => 'btn btn-primary'))}}
                                <a href="{{ route('cmspages.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
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
            height:300,
        });

        jQuery(".title" ).focusout(function(event) {
            var titleElement = jQuery(this).children('#display_name');
            var value        = jQuery(titleElement).val();
            var slugValue    = value.replace(/\s+/g, '-').toLowerCase();
            var slugCss      = jQuery('.slug').css('display');
            jQuery('.slug').children('input').val(slugValue);
            if(slugCss == 'none'){
                jQuery('.slug').css('display', 'block');
            }
        });
    });
</script>
@endsection
