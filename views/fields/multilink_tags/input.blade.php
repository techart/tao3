<div class="tao-field-multilink-tags">
  <textarea
	  name="{{ $field->name }}"
	  class="input text {{ $field->classForInput() }}"
	  style="{!! $field->styleForInput() !!}"
	  {!! $field->renderAttrs() !!}
>{{ $field->inputValue() }}</textarea>
	@if ($field->param('with_links'))
		<div class="tao-field-multilink-tags__links">
			@foreach($field->items() as $tid => $tag)
				<span @if($field->isAttached($tid))class="selected"@endif>{{ $tag }}</span>
			@endforeach
			{{ Assets::useBottomScript('/tao/scripts/fields/multilink-tags.js') }}
		</div>
	@endif
</div>
