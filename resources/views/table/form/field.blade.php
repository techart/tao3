<div class="form-group">
	<label class="col-sm-2 control-label" for="{{ $field->name }}">{!! $field->labelInAdminForm() !!}</label>
	<div class="col-sm-10">
		{!! $field->renderInput() !!}
		@if ($comment = $field->param(['comment_in_admin', 'comment'], false))
			<p class="comment">{!! $comment !!}</p>
		@endif
	</div>
</div>
