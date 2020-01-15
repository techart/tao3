@include("fields.variants")
@foreach($field->variantsWithDefault() as $code => $vdata)
	<input
		type="{{ $field->typeForInput() }}"
		name="{{ $field->name }}{{ $vdata['postfix'] }}"
		class="input string form-control input-{{ $field->name }}-variant input-{{ $field->name }}-variant-{{ $code }} {{ $field->classForInput() }}"
		style="{!! trim($field->styleForInput(), ';') !!}{!! ($code=='default'? '' : ';display:none;') !!}"
		value="{!! htmlspecialchars($field->variantValue($code)) !!}"
		{!! $field->renderAttrs() !!}
	>
@endforeach