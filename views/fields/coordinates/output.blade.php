<?php
/**
 * @var \TAO\Fields\Type\Coordinates $field
 */
?>
@if ($field->isNotEmpty())
	@script('https://api-maps.yandex.ru/2.1/?lang=ru_RU')
	@script('/tao/scripts/jquery-3.3.1.min.js')
	@script('/tao/scripts/fields/coordinates/public.js')

	@style('/tao/styles/fields/coordinates/public.css')

	<div id="{{ $field->getMapId() }}" class="b-coords-output" data-coords="{{ json_encode($field->value()) }}"
		 data-zoom="{{ $zoom }}"></div>
@endif
