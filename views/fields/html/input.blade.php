<?php
/**
 * @var $field App\Fields\Type\Html
 * @var $item \TAO\ORM\Model
 * @var $settings array
 **/

Assets::useFile('/tao/scripts/tinymce/tinymce.min.js');
?>

<div class="control-group">
		<textarea name="{{ $field->name }}" id="{{ $field->editorID() }}"
				  class="input text tinyMCE-container {{ $field->classForInput() }}"
				  style="{!! $field->styleForInput() !!}" {!! $field->renderAttrs() !!}>{{ $field->value() }}</textarea>
</div>

<script>
	document.addEventListener("DOMContentLoaded", function () {
		tinymce.init(@json($field->editorConfig()));
	});
</script>
