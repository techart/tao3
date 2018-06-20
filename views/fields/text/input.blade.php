@include("fields.variants")
@foreach($field->variantsWithDefault() as $code => $vdata)
	<textarea
		name="{{ $field->name }}{{ $vdata['postfix'] }}"
		class="input text input-{{ $field->name }}-variant input-{{ $field->name }}-variant-{{ $code }}{{ $field->tabKeyClass() }} {{ $field->classForInput() }}"
		style="{!! trim($field->styleForInput(), ';') !!}{!! ($code=='default'? '' : ';display:none;') !!}"
		{!! $field->renderAttrs() !!}
	>{{ htmlspecialchars($field->variantValue($code)) }}</textarea>
@endforeach