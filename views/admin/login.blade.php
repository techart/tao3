<!DOCTYPE html>
<html>
  <head>
    <title>Admin Login</title>
    <!-- Bootstrap -->
    <link href="/tao/styles/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="/tao/styles/bootstrap-responsive.min.css" rel="stylesheet" media="screen">
    <link href="/tao/styles/styles.css" rel="stylesheet" media="screen">
     <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <script src="/tao/scripts/modernizr-2.6.2-respond-1.1.0.min.js"></script>
  </head>
  <body id="login">
    <div class="container">

      <form class="form-signin" method="POST" action="{{ url('/admin/login') }}">
        {{ csrf_field() }}
        <h2 class="form-signin-heading">Авторизация</h2>
        <input id="email" type="email" name="email" class="input-block-level" placeholder="E-Mail">
        <input id="password" name="password" type="password" class="input-block-level" placeholder="Пароль">
        <label class="checkbox">
          <input type="checkbox" value="1" name="remember"> Запомнить меня
        </label>
        <button class="btn btn-large btn-primary" type="submit">Вход</button>
      </form>

    </div> <!-- /container -->
    <script src="/tao/scripts/jquery-1.9.1.min.js"></script>
    <script src="/tao/scripts/bootstrap.min.js"></script>
  </body>
</html>