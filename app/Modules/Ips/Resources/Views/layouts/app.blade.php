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
                    {{ Html::image('final-logo.png', 'Indoor Positioning System', array( 'width' => 250)) }}
                </a>
            </div>
            <div class="collapse navbar-collapse" id="app-navbar-collapse">
                <!-- Left Side Of Navbar -->
                <ul class="nav navbar-nav">
                    <li {{ Request::segment(2) == 'floorplan' ? 'class=active' : '' }}><a href="{{ route('floorplan.index') }}">Floorplan</a></li>
                </ul>
            </div>
        </div>
    </nav>
@endsection

