<input
    type="{{ $field->typeForInput() }}"
    name="{{ $field->name }}"
    class="input string {{ $field->classForInput() }}"
    style="{!! $field->styleForInput() !!}"
    value="{{ $item[$field->name] }}"
	{!! $field->renderAttrs() !!}
>
