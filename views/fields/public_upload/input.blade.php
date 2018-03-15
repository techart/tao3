<input
        type="file"
        name="{{ $field->name }}"
        class="input upload {{ $field->classForInput() }}"
        style="{!! $field->styleForInput() !!}"
        value="1"
        {!! $field->renderAttrs() !!}
>
