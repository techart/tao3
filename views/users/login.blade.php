@extends('~layout')

@section('content')
<h1>Вход</h1>
<form class="form-signin" method="POST" action="{{ url('/users/login/') }}">
  {{ csrf_field() }}
  <input id="email" type="email" name="email" class="input-block-level" placeholder="E-Mail">
  <input id="password" name="password" type="password" class="input-block-level" placeholder="Пароль">
  <label class="checkbox">
    <input type="checkbox" value="1" name="remember"> Запомнить меня
  </label>
  <button class="btn btn-large btn-primary" type="submit">Вход</button>
</form>
<a href="/login/social/yandex/">Y</a>
<a href="/users/register/">Reg</a>
@endsection