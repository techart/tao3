@foreach($items as $k => $v)
    <label class="label {{ $field->name }}-label {{ $field->labelClass()  }}" for="{{ $field->name }}_{{ $k }}">{{ $v }}</label>
    <input
            type="radio"
            name="{{ $field->name }}"
            id="{{ $field->name }}_{{ $k }}"
            class="input radiobutton {{ $field->classForInput() }}"
            style="{!! $field->styleForInput() !!}"
            value="{{ $k }}"
            @if ($k==$item[$field->name]) checked @endif
            {!! $field->renderAttrs() !!}
    >
@endforeach
