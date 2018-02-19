<textarea
	name="{{ $field->name }}"
	class="input text {{ $field->classForInput() }}"
	style="{!! $field->styleForInput() !!}"
	{!! $field->renderAttrs() !!}
>{{ htmlspecialchars($item[$field->name]) }}
</textarea>
