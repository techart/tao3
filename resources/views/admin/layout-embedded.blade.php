@include('admin.setup')
<!DOCTYPE html>
<html>

    <head>
        <meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
        {!! Assets::meta() !!}
        <!-- Bootstrap1 -->
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

    <body class="embedded"><div class="sizer">
        <div class="content" id="content">@yield('content')</div>
        <script src="/tao/scripts/bootstrap.min.js"></script>
        {!! Assets::bottomScripts() !!}
        {!! Assets::textBlock('bottom') !!}
    </div></body>
    <script>
		function getWindowHeight() {
			return $('.sizer').outerHeight(true);
		}
    </script>
</html>