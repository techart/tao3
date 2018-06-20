<?php
/**
 * @var $field \TAO\Fields\Type\MultiCheckbox
 * @var $item \TAO\ORM\Model
 * @var $settings array
 */
?>

<div class="multilink-items multilink-items--static">
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
			<span class="multilink-item-label">{{ $title }}</span>
		</label>
	@endforeach
</div>


