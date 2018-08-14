@if ($field->renderable())
	<div class="{{ $container_class }}">
		@foreach($field->renderableEntries() as $entry)
			@include($entry_template)
		@endforeach
	</div>
@endif