<input
        type="file"
        name="{{ $field->name }}"
        class="input upload form-control {{ $field->classForInput() }}"
        style="{!! $field->styleForInput() !!}"
        value="1"
        {!! $field->renderAttrs() !!}
>
