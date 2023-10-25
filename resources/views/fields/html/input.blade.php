<?php
/**
 * @var $field App\Fields\Type\Html
 * @var $item \TAO\ORM\Model
 * @var $settings array
 **/

Assets::useFile('/tao/scripts/tinymce/tinymce.min.js');
$variants = $field->variants();
?>

@style('/tao/styles/fields/variants.css')
@foreach($field->variantsWithDefault() as $code => $vdata)
	@if ($variants)
	<div class="b-variants__label b-variants__label-{{ $code }}">{{ $vdata['label'] }}</div>
	@endif
	<textarea name="{{ $field->name }}{{ $vdata['postfix'] }}" id="{{ $field->editorID($code) }}"
			  class="input text tinyMCE-container form-control input-{{ $field->name }}-variant input-{{ $field->name }}-variant-{{ $code }} {{ $field->classForInput() }}"
			  style="{!! $field->styleForInput() !!}"
			  {!! $field->renderAttrs() !!}>{{ $field->variantValue($code) }}</textarea>
	<script>
		document.addEventListener("DOMContentLoaded", function () {
				tinymce.init(@json($field->editorConfig($code)));
		});
	</script>
@endforeach
