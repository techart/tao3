<?php
/** @var \TAO\Fields\Type\Pairs $field */

Assets::useStyle('/tao/styles/fields/pairs.css');
Assets::useScript('/tao/scripts/fields/pairs.js');
?>


<div class="b-pairs" data-remove-confirm="@lang('fields.remove_multiple_confirmation')"
     style="{!! $field->styleForAdminInput() !!}">
	<table class="b-pairs__table">
		<thead>
			<tr>
				<th class="b-pairs__heading">
					<button class="b-pairs__delete" type="button"><i class="icon-remove"></i></button>
				</th>
				<th class="b-pairs__heading" style="{!! $field->styleForCol('key') !!}">
					{!! $field->keyCaption() !!}
				</th>
				<th class="b-pairs__heading" style="{!! $field->styleForCol('value') !!}">
					{!! $field->valueCaption() !!}
				</th>
			</tr>
		</thead>
		<tbody>
			@foreach($field->value() as $row)
				<tr class="b-pairs__row">
					<td class="b-pairs__cell">
						<input class="b-pairs__input b-pairs__input--checkbox" type="checkbox" tabindex="-1">
					</td>
					<td class="b-pairs__cell" style="{!! $field->styleForCol('key') !!}">
						<input class="b-pairs__input b-pairs__input--text b-pairs__input--key"
							   name="{{ $field->nameForKey($loop->index) }}" type="text" value="{{ $row['key'] }}">
					</td>
					<td class="b-pairs__cell" style="{!! $field->styleForCol('value') !!}">
						<input class="b-pairs__input b-pairs__input--text b-pairs__input--value"
							   name="{{ $field->nameForValue($loop->index) }}" type="text" value="{{ $row['value'] }}">
					</td>
				</tr>
			@endforeach

			<tr class="b-pairs__row b-pairs__row--blank">
				<td class="b-pairs__cell">
					<input class="b-pairs__checkbox b-pairs__input--checkbox" type="checkbox" tabindex="-1">
				</td>
				<td class="b-pairs__cell" style="{!! $field->styleForCol('key') !!}">
					<input class="b-pairs__input b-pairs__input--text b-pairs__input--key"
					       name="{{ $field->nameForKey("blank") }}" type="text"
					       placeholder="@lang("fields.new_key")">
				</td>
				<td class="b-pairs__cell" style="{!! $field->styleForCol('value') !!}">
					<input class="b-pairs__input b-pairs__input--text b-pairs__input--value"
					       name="{{ $field->nameForValue("blank") }}" type="text"
					       placeholder="@lang("fields.new_value")">
				</td>
			</tr>
		</tbody>
	</table>
	
	<button class="b-pairs__add-button btn btn-info" type="button">@lang('fields.add')</button>
</div>
