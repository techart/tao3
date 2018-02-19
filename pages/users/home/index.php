<?php return \TAO::authorized(function() { ?>
  <h1><?= Auth::user()->name ?></h1>
  <a href="/users/logout/">Logout</a>
<?php });
