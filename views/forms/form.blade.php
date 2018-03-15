@style('/tao/styles/forms.css')
@if ($ajax)
  @include($form->templateAjax($__data))
@endif

<form
        id="{{ $form->htmlId($__data) }}"
        class="{{ $form->formClass($__data) }}"
        method="{{ $form->formMethod($__data) }}"
        action="{!! $form->action($__data) !!}"
        enctype="{{ $form->formEnctype($__data) }}">
  @if ($ajax)
    <ul class="ajax-errors"></ul>
  @endif
  {{ csrf_field() }}<input type="hidden" name="_session_key" value="{{ $session_key }}">
  @include($form->templateFields($__data))
  @include($form->templateSubmit($__data), ['settings' => $form->submitButtonSettings()])
</form>
