<div class="multilink-items {!! $field->classForAdminInput() !!}" style="{!! $field->styleForAdminInput() !!}">
    @foreach($field->items() as $id => $title)
        <label class="multilink-item">
            <input
                type="checkbox"
                name="{{ $field->name }}[{{ $id }}]"
                class="input checkbox {{ $field->classForInput() }}"
                value="1"
                @if ($field->isAttached($id))
                checked
                @endif
                {!! $field->renderAttrs() !!}
            >
            <span class="multilink-item-custom-checkbox"></span>
            <span class="multilink-item-label">{!! $title !!}</span>
        </label>
    @endforeach
</div>
