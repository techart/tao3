@if ($variants = $field->variants())
	@style('/tao/styles/fields/variants.css')
	@bottomScript('/tao/scripts/fields/variants.js')
	<div class="b-variants" data-field="{{ $field->name }}">
		@foreach($variants as $code => $data)
			<button class="b-variants__button b-variants__button-{{ $code }}{{ ($code=='default'? ' b-variants__button--current' : '') }}" data-code="{{ $code }}">{{ $data['label'] }}</button>
		@endforeach
	</div>
@endif