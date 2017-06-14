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
                    {{ Html::image('final-logo.png', 'Attendance System', array( 'width' => 200)) }}
                </a>
            </div>
            <div class="collapse navbar-collapse" id="app-navbar-collapse">
                <!-- Left Side Of Navbar -->
                <ul class="nav navbar-nav">
                    <li {{ Request::segment(2) == 'course' ? 'class=active' : '' }}><a href="{{ route('course.index') }}">Courses</a></li>
                    <li {{ Request::segment(2) == 'semester' ? 'class=active' : '' }}><a href="{{ route('semester.index') }}">Semesters</a></li>
                    <li {{ Request::segment(2) == 'subject' ? 'class=active' : '' }}><a href="{{ route('subject.index') }}">Subjects</a></li>
                    <li {{ Request::segment(2) == 'class' ? 'class=active' : '' }}><a href="{{ route('class.index') }}">Class Rooms</a></li>
                    <li {{ Request::segment(2) == 'lectures' ? 'class=active' : '' }}><a href="{{ route('lectures.index') }}">Lectures</a></li>
                    <li {{ Request::segment(2) == 'students' ? 'class=active' : '' }}><a href="{{ route('students.index') }}">Students</a></li>
                    <li {{ Request::segment(2) == 'notifications' ? 'class=active' : '' }}><a href="{{ route('notifications.index') }}">Notifications</a></li>
                    <li {{ Request::segment(2) == 'teachers' ? 'class=active' : '' }}><a href="{{ route('teachers.index') }}">Teachers</a></li>
                </ul>
            </div>
        </div>
    </nav>
@endsection

