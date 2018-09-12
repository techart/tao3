<div class="row" style="margin-top:20px;margin-bottom:20px;">

	@if ($count>0)
		<div class="rows-count col-md-2">
			@include('table ~ list.count')
		</div>
	@endif

	@if ($numpages>1)
		@if ($count>0)
		<div class="page-navigator page-navigator-top col-md-10">
		@else
		<div class="page-navigator page-navigator-top col-md-12">
		@endif
			@include('pager ~ admin')
		</div>
	@endif

</div>