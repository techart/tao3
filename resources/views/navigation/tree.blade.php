@php
	$__level = $__level ?? 0;
@endphp
<ul data-level="{{ $__level }}" {!! $args ?? '' !!}>
@foreach ($links as $link)
	@if($link->checkAccess())
		<li>
			@include('navigation ~ link')
			@if ($link->count())
				{!! $link->render('tree', ['__level' => $__level+1]) !!}
			@endif
		</li>
	@endif
@endforeach
</ul>