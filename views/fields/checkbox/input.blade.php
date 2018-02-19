<input
	type="checkbox"
	name="{{ $field->name }}"
	class="input checkbox {{ $field->classForInput() }}"
	style="{!! $field->styleForInput() !!}"
	value="1"
	@if ($field->checked())
	checked
	@endif
	{!! $field->renderAttrs() !!}
>
