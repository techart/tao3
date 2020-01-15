@foreach($additional_actions as $aaction => $adata)
	<a href="{!! url($adata['url']) !!}" class="{{ $adata['button'] }}"@if (isset($adata['title'])) title="{!! $adata['title'] !!}"@endif>
		@if (isset($adata['icon']))
			<i class="{{ $adata['icon'] }}"></i>
		@endif
		@if (isset($adata['label']))
			{!! $adata['label'] !!}
		@endif
	</a>
@endforeach