@include('admin.setup')
<!DOCTYPE html>
<html>

    <head>
        <meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
        {!! Assets::meta() !!}
        <!-- Bootstrap -->
        <link href="/tao/styles/bootstrap.min.css" rel="stylesheet" media="screen">
        <link href="/tao/styles/admin.css" rel="stylesheet" media="screen">
        <link href="/tao/styles/buttons.css" rel="stylesheet" media="screen">
        {!! Assets::styles() !!}
        <!--[if lte IE 8]><script language="javascript" type="text/javascript" src="/tao/scripts/excanvas.min.js"></script><![endif]-->
        <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
            <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
        <script src="/tao/scripts/jquery-1.9.1.min.js"></script>
        <script src="/tao/scripts/modernizr-2.6.2-respond-1.1.0.min.js"></script>
        <script src="/tao/scripts/admin/index.js"></script>
        {!! Assets::scripts() !!}
    </head>

    <body>
        <nav class="navbar navbar-default navbar-fixed-top">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#top-navbar-collapse" aria-expanded="false">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    @if(Auth::check())
                    <a class="navbar-brand" href="#">{!! config('app.name', 'Laravel') !!}</a>
                    @else
                    <a class="navbar-brand" href="javascript:void()">Авторизация</a>
                    @endif
                </div>

                @if(Auth::check())
                <div class="collapse navbar-collapse" id="top-navbar-collapse">
                    @if(!Auth::user()->isBlocked)
                      {!! TAO::navigation('admin')->render('admin') !!}
                    @endif

                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown">
                            <a href="javascript:void()" role="button" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="icon-user"></i> {{ Auth::user()->name }} <i class="caret"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a tabindex="-1" href="{{ url('/admin/logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">Выход</a>
                                </li>
                            </ul>
                            <form id="logout-form" action="{{ url('/admin/logout') }}" method="POST" style="display: none;">{{ csrf_field() }}</form>
                        </li>
                    </ul>
                </div>
                <!--/.nav-collapse -->
                @endif
            </div>
        </nav>
        <div class="container-fluid">

            @hasSection('h1')
                <div class="row">
                    <div class="col-md-9" id="content-h1"><h1 class="main">@yield('h1')</h1></div>
                    <div class="col-md-3 right-buttons" id="content-right-buttons">@yield('right_buttons')</div>
                </div>
            @endif

            <div class="row">
                @hasSection('sidebar')
                    <div class="{{ $sidebar_visible? 'col-md-3' : 'unvisible' }} pull-right" id="content-sidebar">@yield('sidebar')</div>
                    <div class="{{ $sidebar_visible? 'col-md-9' : 'col-md-12' }} pull-left" id="content">@yield('content')</div>
                @else
                    <div class="col-md-12" id="content">@yield('content')</div>
                @endif
            </div>

            <hr>
            <footer>
                @include('admin.footer')
            </footer>
        </div>

        <script src="/tao/scripts/bootstrap.min.js"></script>
        {!! Assets::bottomScripts() !!}
        {!! Assets::textBlock('bottom') !!}
    </body>

</html>