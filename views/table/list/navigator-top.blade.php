<div class="navigator-top">

	@if ($count>0)
		<div class="rows-count">
			@include('table ~ list.count')
		</div>
	@endif

	@if ($numpages>1)
		<div class="page-navigator page-navigator-top">
			@include('pager ~ admin')
		</div>
	@endif

</div>