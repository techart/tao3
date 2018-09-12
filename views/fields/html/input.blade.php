<?php
/**
 * @var $field App\Fields\Type\Html
 * @var $item \TAO\ORM\Model
 * @var $settings array
 **/

Assets::useFile('/tao/scripts/tinymce/tinymce.min.js');
?>

<textarea name="{{ $field->name }}" id="{{ $field->editorID() }}"
		  class="input text tinyMCE-container form-control {{ $field->classForInput() }}"
		  style="{!! $field->styleForInput() !!}" {!! $field->renderAttrs() !!}>{{ $field->value() }}</textarea>

<script>
	document.addEventListener("DOMContentLoaded", function () {
		tinymce.init(@json($field->editorConfig()));
	});
</script>
