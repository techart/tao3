@style('/tao/styles/fields/huge-multilink.css')
@script('/tao/scripts/fields/huge-multilink.js')
<div class="tao-field-huge-multilink" data-url="{!! $field->apiUrl('search') !!}" data-add-url="{!! $field->apiUrl('additem') !!}">
	<input class="tao-field-huge-multilink__hidden" type="hidden" name="{{ $field->name }}" value="">
	<div class="tao-field-huge-multilink__items">
		@foreach($field->attachedItems() as $item)<div class="tao-field-huge-multilink__item attached-id-{{ $item->id }}">
				<p>{!! $item->title() !!}</p>
				<em data-id="{{ $item->id  }}">&nbsp;</em>
			</div>@endforeach
		<button class="tao-field-huge-multilink__add btn-success btn-primary btn"><i class="icon-white icon-plus"></i></button>
	</div>
	<div class="tao-field-huge-multilink__search">
		<input class="tao-field-huge-multilink__search-input" type="text" placeholder="{{ $field->searchPlaceholder() }}" value="">
		<div class="tao-field-huge-multilink__search-select" data-uid="{{ uniqid() }}"></div>
	</div>
</div>
