<div class="b-subscribe-form">
	@isset($title)
	<div class="b-subscribe-form__title">{!! $title !!}</div>
	@endif
	
	<div class="b-subscribe-form__form">
		<form action="{!! $submit_url !!}" method="post">
			{{ csrf_field() }}
			<input type="hidden" name="encrypted_data" value="{!! $encrypted_data !!}" />
			<input type="email" name="email" placeholder="" class="b-subscribe-form__email-input" value="" />
			<input type="submit" class="b-subscribe-form__submit" value="" />
		</form>
	</div>
</div>