<?php
/**
 * @var $field App\Fields\Type\Html
 * @var $item \TAO\ORM\Model
 * @var $settings array
 *
 * @var $editorID string Уникальный идентификатор поля
 **/
$editorID = $field->editorID();

Assets::useFile('/tao/scripts/tinymce/tinymce.min.js');
Assets::useFile('/tao/scripts/fields/html/script.js', 'bottom_scripts');
?>

<div class="control-group">
		<textarea name="{{ $field->name }}" id="{{ $editorID }}"
				  class="input text tinyMCE-container {{ $field->classForInput() }}"
				  style="{!! $field->styleForInput() !!}" {!! $field->renderAttrs() !!}>{{ $field->value() }}</textarea>
</div>
<div class="control-group">
	<a href="#" class="btn btn-info editor-mode-switcher" data-for="{{ $editorID }}" data-text="Текст"
	   data-html="HTML">Текст</a>
</div>

<script>
	window['editorsSettings'] = window['editorsSettings'] || {};
	window['editorsSettings'].{{ $editorID }} = @json($field->editorConfig());
</script>
