@extends('~layout')
@php
	/** @var \TAO\ORM\Model\Vacancy $item */

	$block = \TAO::frontend()->block('vacancy');
@endphp
@section('content')
	<div class="{{ $block }}">
		<h2 class="{{ $block->elem('title') }}">{!! $item->field('title') !!}</h2>
		<div class="{{ $block->elem('info') }}">
			@if(!$item->field('activity')->value())
				<p class="{{ $block->elem('off') }}">Вакансия в данный момент не доступна</p>
			@endif
			<p class="{{ $block->elem('city') }}">Город: {{ $item->field('city') }}</p>
			<p class="{{ $block->elem('salary') }}">Оплата: {{ $item->field('salary') }}</p>
		</div>
		<p><strong>Требования:</strong></p>
		<ul class="{{ $block->elem('list')->mod('requirements') }}">
			@foreach($item->convertFieldToList('requirements',$item->id) as $line)
				<li class="{{ $block->elem('list-item') }}">{{ $line }}</li>
			@endforeach
		</ul>
		<p><strong>Обязанности:</strong></p>
		<ul class="{{ $block->elem('list')->mod('duties') }}">
			@foreach($item->convertFieldToList('duties', $item->id) as $line)
				<li class="{{ $block->elem('list-item') }}">{{ $line }}</li>
			@endforeach
		</ul>
	</div>
	{!! dt('vacancies_form')->renderForm(['vacancy' => $item->field('title')->render()]) !!}
@endsection