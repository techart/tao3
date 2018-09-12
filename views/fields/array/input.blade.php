<textarea
	name="{{ $field->name }}"
	class="input text form-control {{ $field->tabKeyClass() }} {{ $field->classForInput() }}"
	style="{!! trim($field->styleForInput(), ';') !!}"
	{!! $field->renderAttrs() !!}
>{!! $field->valueSrc() !!}</textarea>
