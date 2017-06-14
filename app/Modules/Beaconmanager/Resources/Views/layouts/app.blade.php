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
                    {{ Html::image('final-logo.png', 'Infra Structure', array( 'width' => 150)) }}
                </a>
            </div>
            <div class="collapse navbar-collapse" id="app-navbar-collapse">
                <!-- Left Side Of Navbar -->
                <ul class="nav navbar-nav">
                    {{--<li {{ Request::segment(2) == 'manufacture' ? 'class=active' : '' }}><a href="{{ route('manufacture.index') }}">Manufacturer</a></li>--}}
                    <li {{ Request::segment(2) == 'iot' ? 'class=active' : '' }}><a href="{{ route('iot.index') }}">Iot Gateways</a></li>
                    <li {{ Request::segment(2) == 'beacon' ? 'class=active' : '' }}><a href="{{ route('beacon.index') }}">Beacons</a></li>
                </ul>
            </div>
        </div>
    </nav>
@endsection

