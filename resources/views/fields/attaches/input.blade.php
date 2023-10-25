<?php
$variants = $field->variants();
?>
@style('/tao/styles/fields/variants.css')
@foreach ($field->extraCSS() as $file)
    @style($file)
@endforeach
@foreach ($field->extraJS() as $file)
    @bottomScript($file)
@endforeach
@foreach($field->variantsWithDefault() as $code => $vdata)
    @if ($variants)
    <div class="b-variants__label b-variants__label-{{ $code }}">{{ $vdata['label'] }}</div>
    @endif
    <div class="tao-fields-attaches">
        <input type="hidden" name="{{ $field->name }}{{ $vdata['postfix'] }}" value="{{ $field->tempId() }}" id="tao_attaches_hidden_{{ $field->name }}{{ $vdata['postfix'] }}">
        <div class="{{ $field->filelistClass() }}" id="tao_attaches_filelist_{{ $field->name }}{{ $vdata['postfix'] }}"></div>
        <div class="tao-fields-attaches-informer" id="tao_attaches_informer_{{ $field->name }}{{ $vdata['postfix'] }}"></div>
        <div class="tao-fields-attaches__upload">
            <a class="btn btn-info tao-fields-attaches__upload-btn" href="javascript:void();" id="tao_attaches_button_{{ $field->name}}{{ $vdata['postfix'] }}">Загрузить</a>
            <input class="tao-fields-attaches__upload-input" type="file" name="{{ $field->name }}{{ $vdata['postfix'] }}-files" multiple>
        </div>
        @include("fields ~ attaches.{$field->templateJS()}")
    </div>
@endforeach
