<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Indolytics Saas') }}</title>
    {!! Html::script('/js/jquery-1.12.4.js') !!}
    {{--{{ Html::script('../resources/assets/js/Drag/jquery-1.10.2.js') }}--}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css"
          crossorigin="anonymous">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700">
    {{ Html::style('/css/font-awesome.min.css') }}
    {{ Html::style('/css/summernote.css') }}
    {{ Html::style('/css/datepicker/jquery-ui.css') }}
    {{ Html::style('/css/timepicker/jquery.timepicker.css') }}
    {{ Html::style('/css/sidebar.css') }}
    {{ Html::style('/css/selectboxcss/bootstrap-select.min.css') }}

    {{ Html::script('/js/Drag/jquery-ui.js') }}
    {{ Html::script('/js/dataTables.bootstrap.min.js') }}

    {{ Html::script('/js/selectboxjs/bootstrap-select.min.js') }}
    {{ Html::script('/js/timepicker/jquery.timepicker.js') }}
    {{ Html::script('/js/datepicker/jquery-ui.js') }}
    {{ Html::style('/css/jquery.dataTables.css') }}
    {{ Html::script('/js/jquery.dataTables.min.js') }}
    {{ Html::script('/js/summernote.min.js') }}
    <!-- Styles -->
    {{ Html::style('/css/app.css') }}

<!-- Scripts -->
<style type="text/css">
.navbar-default{
    margin-bottom : 0px !important;
}
</style>
    <script>
        window.Laravel = <?php echo json_encode([
            'csrfToken' => csrf_token(),
        ]); ?>

        $(document).ready(function(){
            $( ".alert-success" ).delay(3000).slideUp('slow');
            $( ".alert-danger" ).delay(3000).slideUp('slow');
        })
    </script>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-default navbar-static-top navbar-main-menu">
            <div class="container">
                <div class="navbar-header">

                    <!-- Collapsed Hamburger -->
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                        <span class="sr-only">Toggle Navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/') }}">
                        {{ config('app.name', 'Indolytics Saas') }}
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <!-- Left Side Of Navbar -->
                    <ul class="nav navbar-nav">
                        @if (Auth::user())
                        <li {{ Request::segment(1) == 'beaconmanager' ? 'class=active' : '' }}><a href="{{ url('/beaconmanager/iot') }}">Infra Structure</a></li>
                            <li {{ Request::segment(1) == 'proximity' ? 'class=active' : '' }}><a href="{{ url('/proximity/adsManager') }}">Proximity</a></li>
                        <li {{ Request::segment(1) == 'events' ? 'class=active' : '' }}><a href="{{ url('/events/events') }}">Events & Exhibition</a></li>
                        <li {{ Request::segment(1) == 'ips' ? 'class=active' : '' }}><a href="{{ url('/ips/floorplan') }}">Indoor Positioning System</a></li>
                        <li {{ Request::segment(1) == 'attendance' ? 'class=active' : '' }}><a href="{{ url('/attendance/course') }}">Attendance System</a></li>
                    {{--<li {{ Request::segment(1) == 'storesolution' ? 'class=active' : '' }}><a href="{{ url('/storesolution/') }}">Store</a></li>--}}
                            <li {{ Request::segment(1) == 'projects' ? 'class=active' : '' }}><a href="{{ url('/projects') }}">Projects</a></li>
                        @endif
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-right">
                        <!-- Authentication Links -->
                        @if (Auth::guest())
                            <li><a href="{{ url('/login') }}">Login</a></li>
                            <li><a href="{{ url('/register') }}">Register</a></li>
                        @else
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu" role="menu">
                                    <li>
                                        <a href="{{ url('/logout') }}"
                                            onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                            Logout
                                        </a>

                                        <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </nav>
        @yield('submenu')
        @if(Session::has('flash_message'))
            <div class="alert alert-success">
                {{ Session::get('flash_message') }}
            </div>
        @endif
        @if(Session::has('delete_message'))
            <div class="alert alert-danger">
                {{ Session::get('delete_message') }}
            </div>
        @endif
        @yield('content')
    </div>

    <!-- Scripts -->
    {{ Html::script('/js/app.js') }}
    <script>
        $(document).ready(function(){
            reOffset();
        })
    </script>
</body>
</html>
