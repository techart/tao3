@if (request()->get('is_embedded'))
	<a href="{!! url($list_url) !!}" class="button-in-embedded button-in-embedded-list">{{ $item->adminReturnToListText() }}</a>
@endif
<form id="admin-form" action="" method="post" enctype="multipart/form-data">
	{{ csrf_field() }}
	    
	@if($tabs)
		@include('table ~ form.tabs')
	@else
		@include('table ~ form.fields', [ 'tab' => '#', 'tab_label' => '#'])
	@endif
	
	@if ($submit_text)    
	<button type="submit" class="btn btn-primary"><i class="icon-ok icon-white"></i> {!! $submit_text !!}</button>
	@endif
	
	@if ($submit_and_stay_text)    
	<button name="_submit_and_stay" value="1" type="submit" class="btn btn-primary">
		<i class="icon-ok icon-white"></i> {!! $submit_and_stay_text !!}
	</button>
	@endif
	
</form>