@extends('~layout')

@section('content')
	<h1>Подписка на новости</h1>
	<p>
		E-Mail <strong>{{ $email }}</strong> добавлен в список рассылки.
		@if ($letter_sent)
			Выслано письмо с запросом на подтверждение подписки.
		@endif
	</p>
@endsection