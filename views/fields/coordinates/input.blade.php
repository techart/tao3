<?php
/**
 * @var \TAO\Fields\Type\Coordinates $field
 */

// TODO: move js params from template
?>
@style('/tao/styles/fields/coordinates/admin.css')
@script('/tao/scripts/fields/coordinates/admin.js')

@style('/tao/styles/leaflet.css')
@script('/tao/scripts/leaflet.js')

<div class="b-field-coordinates" data-field-params="{{ $field->jsFieldParams() }}">
	<div class="b-field-coordinates__inputs">
		<div class="b-field-coordinates__lat-column">
			<input
					type="text"
					name="{{ $field->latInputName() }}"
					class="b-field-coordinates__input-lat input integer {{ $field->classForInput() }}"
					style="{!! $field->styleForInput() !!}"
					value="{{ $field->lat() }}"
					{!! $field->renderAttrs() !!}
					id="{{ $field->getInputId() }}_lat"
			>&nbsp;,&nbsp;
			<label for="{{ $field->getInputId() }}_lat" class="b-field-comments">
				широта, latitude
			</label>
		</div>
		<div class="b-field-coordinates__lng-column">
			<input
					type="text"
					name="{{ $field->lngInputName() }}"
					class="b-field-coordinates__input-lng input integer {{ $field->classForInput() }}"
					style="{!! $field->styleForInput() !!}"
					value="{{ $field->lng() }}"
					{!! $field->renderAttrs() !!}
					id="{{ $field->getInputId() }}_lng"
			>
			<label for="{{ $field->getInputId() }}_lng" class="b-field-comments">
				долгота, longitude
			</label>
		</div>
	</div>
	@if ($show_map)
		<div class="b-field-coordinates__map" id="{{ $field->getInputId() }}_map"></div>
	@endif
</div>
