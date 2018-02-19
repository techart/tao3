<input
	type="{{ $field->typeForInput() }}"
	name="{{ $field->name }}"
	class="input integer {{ $field->classForInput() }}"
	style="{!! $field->styleForInput() !!}"
	value="{{ $item[$field->name] }}"
	{!! $field->renderAttrs() !!}
>
