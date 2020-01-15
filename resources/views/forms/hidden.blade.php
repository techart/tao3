@php
$field = $form->field($field);
@endphp
<input
		type="hidden"
		name="{{ $field->name }}"
		class="input hidden {{ $field->classForInput() }}"
		value="{{ $field->item[$field->name] }}"
		{!! $field->renderAttrs() !!}
>
