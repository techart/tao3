<div class="tao-fields-upload">
	<input type="hidden" name="{{ $field->name }}" value="{{ $field->tempId() }}"
	       id="tao_upload_hidden_{{ $field->name }}">
	<span id="tao_upload_informer_{{ $field->name}}">
        @if ($field->url())
			@if (isset($image))
				<a class="preview"><img src="{{ $field->adminPreviewUrl() }}"></a>
			@endif
			@if ($field->exists())
				<a href="{!! url($field->url()) !!}">Скачать</a> ({{ $field->humanSize() }})
			@endif
			@if ($field->param('can_delete', true))
				<a href="javascript:void();" id="tao_upload_delete_{{ $field->name }}">Удалить</a>
			@endif
		@endif
    </span>
	<br>
	<div class="tao-fields-upload__btn">
		<div class="btn btn-info tao-fields-upload__btn-text" id="tao_upload_button_{{ $field->name}}">
			@if ($field->url())
				Заменить
			@else
				Загрузить
			@endif
		</div>
		<input
			class="tao-fields-upload__file-input"
			type="file"
			name="{{ $field->name }}-file"
			{!! $field->renderAttrs() !!}
		>
	</div>
	@include('fields ~ upload._ajax-upload-script')
</div>
