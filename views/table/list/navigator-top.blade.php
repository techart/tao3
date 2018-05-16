<div class="navigator-top row-fluid">

	@if ($count>0)
		<div class="rows-count span2">
			@include('table ~ list.count')
		</div>
	@endif

	@if ($numpages>1)
		<div class="page-navigator page-navigator-top">
			@include('pager ~ admin')
		</div>
	@endif

</div>