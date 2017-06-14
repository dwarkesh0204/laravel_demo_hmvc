@extends('events::layouts.app')
@section('javascript')
<script type="text/javascript">

jQuery(document).ready(function() {

    jQuery(document).on('change', '.view_name', function()
    {
        var selectedValofView = jQuery(this).val();

        if(selectedValofView == 'cmspage')
        {
            jQuery('.extra_data').css('display', 'block');
        }
        else if(selectedValofView == 'categoryblog')
        {
            jQuery('.cat_id').css('display', 'block');
        }
        else
        {
            jQuery('.extra_data').css('display', 'none');
            jQuery('.cat_id').css('display', 'none');
        }
    });

    jQuery(document).on('change', '.extra_data', function()
    {
        var selectedValue = jQuery(this).val();

        if(selectedValue == 'CmsPage')
        {
            jQuery('.cmsPage').css('display', 'block');
            jQuery('.WebLink').css('display', 'none');
            jQuery('.HTMLContent').css('display', 'none');
        }
        if(selectedValue == 'WebLink')
        {
            jQuery('.WebLink').css('display', 'block');
            jQuery('.cmsPage').css('display', 'none');
            jQuery('.HTMLContent').css('display', 'none');
        }
        if(selectedValue == 'HTMLContent')
        {
            jQuery('.HTMLContent').css('display', 'block');
            jQuery('.cmsPage').css('display', 'none');
            jQuery('.WebLink').css('display', 'none');
        }
        if(selectedValue == '0')
        {
            jQuery('.HTMLContent').css('display', 'none');
            jQuery('.cmsPage').css('display', 'none');
            jQuery('.WebLink').css('display', 'none');
        }
    });

    var selviewNmVal = jQuery(".view_name").val();

    if(selviewNmVal == 'cmspage')
    {
        jQuery('.extra_data').css('display', 'block');
    }
    else if(selviewNmVal == 'categoryblog')
    {
        jQuery('.cat_id').css('display', 'block');
    }
    else
    {
        jQuery('.extra_data').css('display', 'none');
        jQuery('.cat_id').css('display', 'none');
    }

    var selExtraData = jQuery("#extra_data").val();


    if(selExtraData == 'CmsPage')
    {
        jQuery('.cmsPage').css('display', 'block');
        jQuery('.WebLink').css('display', 'none');
        jQuery('.HTMLContent').css('display', 'none');
    }
    if(selExtraData == 'WebLink')
    {
        jQuery('.WebLink').css('display', 'block');
        jQuery('.cmsPage').css('display', 'none');
        jQuery('.HTMLContent').css('display', 'none');
    }
    if(selExtraData == 'HTMLContent')
    {
        jQuery('.HTMLContent').css('display', 'block');
        jQuery('.cmsPage').css('display', 'none');
        jQuery('.WebLink').css('display', 'none');
    }

});

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
                <strong>Page</strong>
            </div>
            <div class="panel-body">
                {!! Form::model($item, ['files' => 'true', 'method' => 'PATCH','route' => ['menu.update', $item->id]]) !!}
                    <div class="box-body">
                        <div class="form-group title">
                            {{Form::label('title', 'Title:*')}}
                            {{Form::text('title',null,array('class' => 'form-control', 'placeholder'=>'Title'))}}
                        </div>

                        <div class="form-group">
                            {{Form::label('descriptions', 'Description:')}}
                            {{Form::textarea('descriptions',null,array('class' => 'form-control', 'rows' => '4', 'cols'=> '50'))}}
                        </div>

                        <div class="form-group">
                            {!! Form::label('view_name', 'View Name*:', ['class' => 'control-label']) !!}
                            {!! Form::select('view_name',
                            array('' => 'Select',
                            'home' => 'Home',
                            'profile' => 'Profile',
                            'navigation' => 'Navigation',
                            'login' => 'Login',
                            'registration' => 'Registration',
                            'event' => 'Event',
                            'session' => 'Session',
                            'myagenda' => 'My Agenda',
                            'mymeeting' => 'My Meeting',
                            'ticket' => 'Ticket',
                            'networking' => 'Networking',
                            'message' => 'Message',
                            'cmspage' => 'cmsPage',
                            'categoryblog' => 'Category Blog'
                            ), null, ['class' => 'form-control view_name','id' => 'view_name']) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::label('parent', 'Parent Menu:', ['class' => 'control-label']) !!}
                            {!! Form::select('parent', $parentMenus, null, ['class' => 'form-control field_type','id' => 'field_type']) !!}
                        </div>

                        <div class="form-group cat_id" style="display:none">
                            {!! Form::label('cat_id', 'Categories:', ['class' => 'control-label']) !!}
                            {!! Form::select('cat_id', (['' => '- Select Category -'] + $Categories), null, ['class' => 'form-control cat_id','id' => 'cat_id']) !!}
                        </div>

                        <div class="form-group row">
                            <div class="col-lg-12">
                                <div class="form-group col-lg-3">
                                    {{Form::label('display_icon', 'Display Icon:')}}
                                    <div class="form-inline">
                                        <div class="radio">
                                            {{ Form::radio('display_icon', '1', true)}}
                                            {{ Form::label('Yes', 'Yes')}}
                                        </div>
                                        <div class="radio">
                                            {{ Form::radio('display_icon', '0', false)}}
                                            {{ Form::label('No', 'No') }}
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-lg-3">
                                    {{Form::label('default_page', 'Default Page:')}}
                                    <div class="form-inline">
                                        <div class="radio">
                                            {{ Form::radio('default_page', '1', true)}}
                                            {{ Form::label('Yes', 'Yes')}}
                                        </div>
                                        <div class="radio">
                                            {{ Form::radio('default_page', '0', false)}}
                                            {{ Form::label('No', 'No') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-lg-3">
                                    {{Form::label('login_requuired', 'Login Requuired:')}}
                                    <div class="form-inline">
                                        <div class="radio">
                                            {{ Form::radio('login_requuired', '1', true)}}
                                            {{ Form::label('Yes', 'Yes')}}
                                        </div>
                                        <div class="radio">
                                            {{ Form::radio('login_requuired', '0', false)}}
                                            {{ Form::label('No', 'No') }}
                                        </div>
                                    </div>
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
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-lg-12">
                                <div class="form-group col-lg-4">
                                    {{Form::label('icon', 'Icon: (Minimum image dimension is 192 X 192)')}}
                                    {{Form::file('icon')}}
                                </div>
                                <div class="form-group col-lg-4">
                                    <div class="form-group">
                                        {!! Form::label('screens', 'Screens*:', ['class' => 'control-label']) !!}
                                        {!! Form::select('screens[]',
                                        array('' => 'Select',
                                        'home' => 'Home',
                                        'profile' => 'Profile',
                                        'navigation' => 'Navigation',
                                        'login' => 'Login',
                                        'registration' => 'Registration',
                                        'event' => 'Event',
                                        'eventdetail' => 'Event Detail',
                                        'session' => 'Session',
                                        'myagenda' => 'My Agenda',
                                        'mymeeting' => 'My Meeting',
                                        'ticket' => 'Ticket',
                                        'networking' => 'Networking',
                                        'message' => 'Message',
                                        'cmspage' => 'cmsPage',
                                        'categoryblog' => 'Category Blog'
                                        ), $screens, ['class' => 'form-control screens','id' => 'screens', 'multiple' => 'multiple']) !!}
                                    </div>
                                </div>
                                <div class="form-group col-lg-4">
                                    <div class="form-group extra_data" style="display:none">
                                        {!! Form::label('extra_data', 'Extra Data:', ['class' => 'control-label']) !!}
                                        {!! Form::select('extra_data',
                                        array('0' => 'Select',
                                        'CmsPage' => 'CmsPage',
                                        'WebLink' => 'WebLink',
                                        'HTMLContent' => 'HTMLContent'
                                        ), null, ['class' => 'form-control extra_data','id' => 'extra_data']) !!}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group cmsPage" style="display:none">
                            {!! Form::label('cmsPage', 'CMS Pages:', ['class' => 'control-label']) !!}
                            {!! Form::select('cmsPage', $cmsPages, $item->cmsPage, ['class' => 'form-control','id' => 'cmsPage']) !!}
                        </div>

                        <div class="form-group webLink" style="display:none">
                            {{Form::label('webLink', 'Web Link:')}}
                            {{Form::text('webLink',null,array('class' => 'form-control', 'placeholder'=>'Please enter web link.'))}}
                        </div>

                        <div class="form-group HTMLContent" style="display:none">
                            {{Form::label('HTMLContent', 'HTML Content:')}}
                            {{Form::textarea('HTMLContent',null,array('class' => 'form-control', 'rows' => '4', 'cols'=> '50', 'placeholder'=>'Please enter HTML Content.','id' => 'technig'))}}
                        </div>
                        <div class="form-group">
                            <div class="pull-right">
                                {{Form::submit('Submit',array('class' => 'btn btn-primary'))}}
                                <a href="{{ route('menu.index') }}"><input type="button" class="btn btn-danger" value="Cancel"></a>
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
