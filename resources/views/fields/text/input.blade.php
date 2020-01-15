@include("fields.variants")
@foreach($field->variantsWithDefault() as $code => $vdata)
	<textarea
		name="{{ $field->name }}{{ $vdata['postfix'] }}"
		class="input text form-control input-{{ $field->name }}-variant input-{{ $field->name }}-variant-{{ $code }}{{ $field->tabKeyClass() }} {{ $field->classForInput() }}"
		style="{!! trim($field->styleForInput(), ';') !!}{!! ($code=='default'? '' : ';display:none;') !!}"
		{!! $field->renderAttrs() !!}
		@if ($field->item->getKey() && $field->param('save_button', false) && $vdata['postfix'] == '')
			data-save-url="{{ $field->apiUrl('save') }}" @bottomScript('/tao/scripts/admin/textarea-save-button.js')
			data-token="{{ csrf_token() }}"
		@endif
	>{!! htmlspecialchars($field->variantValue($code)) !!}</textarea>
@endforeach