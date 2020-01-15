{{ Assets::setMeta('title', 'Регистрация') }}
{{ Assets::setMeta('description', 'Регистрация нового пользователя') }}
@extends('~layout')

@section('content')

<h1>Регистрация</h1>

<form method="POST" action="{{ route('register') }}">
  {{ csrf_field() }}

  <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
    <label for="name">Name</label>
    <input type="text" name="name" value="{{ old('name') }}" required autofocus>
    @if ($errors->has('name'))
       <strong>{{ $errors->first('name') }}</strong>
    @endif
  </div>
  
  <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
    <label for="email">E-Mail Address</label>
    <input type="email" name="email" value="{{ old('email') }}" required>

    @if ($errors->has('email'))
      <strong>{{ $errors->first('email') }}</strong>
    @endif
  </div>

  <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
    <label for="password">Password</label>
    <input type="password" name="password" required>

    @if ($errors->has('password'))
      <strong>{{ $errors->first('password') }}</strong>
    @endif
  </div>

  <div class="form-group">
    <label for="password-confirm">Confirm Password</label>
    <input type="password" name="password_confirmation" required>
  </div>
  
  <div><input type="submit" value="Ok"></div>

</form>

@endsection
