@extends('layouts.app')
@section('submenu')
    <nav class="navbar navbar-default navbar-static-top">
        <div class="container">
            <div class="navbar-header">
                <!-- Collapsed Hamburger -->
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                        data-target="#app-navbar-collapse">
                    <span class="sr-only">Toggle Navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>

                </button>
                <!-- Branding Image -->
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ Html::image('final-logo.png', 'Event', array( 'width' => 150)) }}
                </a>
            </div>
            <div class="collapse navbar-collapse" id="app-navbar-collapse">
                <!-- Left Side Of Navbar -->
                <ul class="nav navbar-nav">
                    <li {{ (Request::segment(2) == 'pollquestion' || Request::segment(2) == 'sessions' || Request::segment(2) == 'userRequestForEvent' || Request::segment(2) == 'events' || Request::segment(2) == 'userRequestForSession' || Request::segment(2) == 'feedback') ? 'class=active' : '' }} class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                            Event <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li {{ Request::segment(2) == 'events' ? 'class=active' : '' }}><a href="{{ route('events.index') }}">Events</a></li>
                            <li {{ Request::segment(2) == 'userRequestForEvent' ? 'class=active' : '' }}><a href="{{ url('events/userRequestForEvent') }}">User request for Event</a></li>
                            <li {{ Request::segment(2) == 'sessions' ? 'class=active' : '' }}><a href="{{ url('events/sessions') }}">Session</a></li>
                            <li {{ Request::segment(2) == 'userRequestForSession' ? 'class=active' : '' }}><a href="{{ url('events/userRequestForSession') }}">User request for Session</a></li>
                            <li {{ Request::segment(2) == 'pollquestion' ? 'class=active' : '' }}><a href="{{ url('events/pollquestion') }}">Poll Questions</a></li>
                            <li {{ Request::segment(2) == 'feedback' ? 'class=active' : '' }}><a href="{{ url('events/feedback') }}">Feedback</a></li>
                        </ul>
                    </li>
                    <li {{ Request::segment(2) == 'users' ? 'class=active' : '' }}><a href="{{ route('users.index') }}">Users</a></li>
                    <li {{ Request::segment(2) == 'roles' ? 'class=active' : '' }}><a href="{{ route('roles.index') }}">Roles</a></li>
                    <li {{ Request::segment(2) == 'menu' ? 'class=active' : '' }}><a href="{{ route('menu.index') }}">Menu</a></li>
                    <li {{ Request::segment(2) == 'category' ? 'class=active' : '' }}><a href="{{ route('category.index') }}">Category</a></li>
                    <li {{ Request::segment(2) == 'cmspages' ? 'class=active' : '' }}><a href="{{ route('cmspages.index') }}">CMS Pages</a></li>
                    <li {{ Request::segment(2) == 'email_template' ? 'class=active' : '' }}><a href="{{ route('email_template.index') }}">Email Template</a></li>
                    <li {{ Request::segment(2) == 'question' ? 'class=active' : '' }}><a href="{{ route('question.index') }}">Questions</a></li>
                    <li {{ Request::segment(2) == 'fields' ? 'class=active' : '' }}><a href="{{ route('fields.index') }}">Form Fields</a></li>
                    <li {{ Request::segment(2) == 'floorplans' ? 'class=active' : '' }}><a href="{{ route('floorplans.index') }}">Floorplan</a></li>
                </ul>
            </div>
        </div>
    </nav>
@endsection

