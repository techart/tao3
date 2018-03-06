@bottomScript('/tao/scripts/fields/password.js')
<span class="tao-fields-password">
	<input
		type="{{ $field->typeForInput() }}"
		name="{{ $field->name }}"
		class="input string {{ $field->classForInput() }}"
		style="{!! $field->styleForInput() !!}"
		value="{{ $item[$field->name] }}"
		{!! $field->renderAttrs() !!}
	>
	<a class="btn btn-inverse" data-url="{!! $field->apiUrl('generate') !!}"><i class="icon-refresh icon-white"></i></a>
</span>
