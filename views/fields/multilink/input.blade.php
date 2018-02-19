<div class="multilink-items">
    @foreach($field->items() as $id => $title)
        <div class="multilink-item">
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
            <span class="multilink-item-label">{{ $title }}</span>
        </div>
    @endforeach
</div>
