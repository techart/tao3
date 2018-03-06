@foreach ($field->extraCSS() as $file)
    @style($file)
@endforeach
@foreach ($field->extraJS() as $file)
    @bottomScript($file)
@endforeach
<div class="tao-fields-attaches">
    <input type="hidden" name="{{ $field->name }}" value="{{ $field->tempId() }}" id="tao_attaches_hidden_{{ $field->name }}">
    <div class="{{ $field->filelistClass() }}" id="tao_attaches_filelist_{{ $field->name }}"></div>
    <div class="tao-fields-attaches-informer" id="tao_attaches_informer_{{ $field->name }}"></div>
    <div class="tao-fields-attaches__upload">
        <a class="btn btn-info tao-fields-attaches__upload-btn" href="javascript:void();" id="tao_attaches_button_{{ $field->name}}">Загрузить</a>
        <input class="tao-fields-attaches__upload-input" type="file" name="{{ $field->name }}-files" multiple>
    </div>
    @include("fields ~ attaches.{$field->templateJS()}")
</div>