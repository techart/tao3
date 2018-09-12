<div class="navigator-bottom row">
	@if ($additional_actions)
		<div class="additional_actions col-md-2">
			@include('table.list.additional-actions')
		</div>
	@endif
	
	@if ($numpages>1)
		@if ($additional_actions)
		<div class="page-navigator page-navigator-bottom col-md-10">
		@else
		<div class="page-navigator page-navigator-bottom col-md-12">
		@endif
			@include('pager ~ admin')
		</div>
	@endif
</div>