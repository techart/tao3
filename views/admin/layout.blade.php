@include('admin.setup')
<!DOCTYPE html>
<html>
    
    <head>
        <meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
        {!! Assets::meta() !!}
        <!-- Bootstrap -->
        <link href="/tao/styles/bootstrap.min.css" rel="stylesheet" media="screen">
        <link href="/tao/styles/bootstrap-responsive.min.css" rel="stylesheet" media="screen">
        <link href="/tao/styles/admin.css" rel="stylesheet" media="screen">
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
        <div class="navbar navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container-fluid">
                    <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse"> <span class="icon-bar"></span>
                     <span class="icon-bar"></span>
                     <span class="icon-bar"></span>
                    </a>
                    @if(Auth::check())
                    <a class="brand" href="#">{!! config('app.name', 'Laravel') !!}</a>
                    
                    
                    <div class="nav-collapse collapse">
                        <ul class="nav pull-right">
                            <li class="dropdown">
                                <a href="javascript:void()" role="button" class="dropdown-toggle" data-toggle="dropdown"> <i class="icon-user"></i> {{ Auth::user()->name }} <i class="caret"></i>

                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a tabindex="-1" href="{{ url('/admin/logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">Выход</a>
                                    </li>
                                </ul>
                                <form id="logout-form" action="{{ url('/admin/logout') }}" method="POST" style="display: none;">{{ csrf_field() }}</form>
                            </li>
                        </ul>
                        @if(!Auth::user()->isBlocked)
                          {!! TAO::navigation('admin')->render('admin') !!}
                        @endif
                    </div>
                    @else
                    <a class="brand" href="javascript:void()">Авторизация</a>
                    @endif
                    <!--/.nav-collapse -->
                </div>
            </div>
        </div>
        <div class="container-fluid">

            @hasSection('h1')
                <div class="row-fluid">
                    <div class="span9" id="content-h1"><h1 class="main">@yield('h1')</h1></div>
                    <div class="span3 right-buttons" id="content-right-buttons">@yield('right_buttons')</div>
                </div>
            @endif
            
            <div class="row-fluid">
                @hasSection('sidebar')
                    <div class="{{ $sidebar_visible? 'span3' : 'unvisible' }} pull-right" id="content-sidebar">@yield('sidebar')</div>
                    <div class="{{ $sidebar_visible? 'span9' : 'span12' }} pull-left" id="content">@yield('content')</div>
                @else
                    <div class="span12" id="content">@yield('content')</div>
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