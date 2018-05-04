@if ($with_datepicker)
	@bottomScript('/tao/scripts/jquery-ui.min.js')
	@style('/tao/styles/jquery-ui.css')
	{{ \Assets::addBottomLine('<script>$(function() {$(".date_input_'.$field->name.'").datepicker({dateFormat: "dd.mm.yy"});});</script>') }}
@endif
<input
	type="{{ $field->typeForInput() }}"
	name="{{ $field->name }}"
	class="date_input_{{ $field->name }} input string {{ $field->classForInput() }}"
	style="{!! $field->styleForInput() !!}"
	value="{{ $item[$field->name]==$field->nullValue()? '' : app('tao.utils')->date($field->generateFormat(), $item[$field->name]) }}"
	{!! $field->renderAttrs() !!}
>
