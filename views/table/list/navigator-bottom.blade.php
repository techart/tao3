<div class="navigator-bottom">
	@if ($additional_actions)
		<div class="additional_actions">
			@include('table.list.additional-actions')
		</div>
	@endif
	
	@if ($numpages>1)
		<div class="page-navigator page-navigator-bottom">
			@include('pager ~ admin')
		</div>
	@endif
</div>