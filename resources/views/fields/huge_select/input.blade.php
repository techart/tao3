@style('/tao/styles/fields/huge-select.css')
@script('/tao/scripts/fields/huge-select.js')
<div class="tao-field-huge-select" data-url="{!! $field->apiUrl('search') !!}" data-add-url="{!! $field->apiUrl('additem') !!}">
    <input class="tao-field-huge-select__hidden" type="hidden" name="{{ $field->name }}" value="{{ $item[$field->name] }}">
    <div class="tao-field-huge-select__visible-value">{!! $field->visibleValue() !!}</div>
    <div class="tao-field-huge-select__search">
        <input class="tao-field-huge-select__search-input" type="text" placeholder="{{ $field->searchPlaceholder() }}" value="">
        <div class="tao-field-huge-select__search-select" data-uid="{{ uniqid() }}"></div>
    </div>
</div>
