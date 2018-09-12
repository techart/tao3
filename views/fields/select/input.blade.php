<select
	name="{{ $field->name }}"
	class="input select form-control {{ $field->classForInput() }}"
	style="{!! $field->styleForInput() !!}"
	value="1"
	{!! $field->renderAttrs() !!}
>
	@foreach($items as $k => $v)
		<option value="{{ $k }}" @if ($k==$item[$field->name]) selected @endif>{!! $v !!}</option>
	@endforeach
</select>
