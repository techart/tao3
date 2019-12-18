@php
	$isEmbedded = request()->get('is_embedded');
	$viewCount = ($count > 0) && !$isEmbedded;
	$viewAdd = $can_add && $isEmbedded;
	$viewLeft = $viewCount || $viewAdd;
@endphp
<div class="row table-navigator-top">

	@if ($viewLeft)
		<div class="rows-count col-md-2">
			@if ($viewAdd)
				<a href="{{ url($controller->actionUrl('add')) }}" class="button-in-embedded button-in-embedded-add">{{ $add_text }}</a>
			@endif
			
			@if ($viewCount)
				@include('table ~ list.count')
			@endif
		</div>
	@endif

	@if ($numpages > 1)
		@if ($viewLeft)
			<div class="page-navigator page-navigator-top col-md-10">
		@else
			<div class="page-navigator page-navigator-top col-md-12">
		@endif
			@include('pager ~ admin')
		</div>
	@endif

</div>