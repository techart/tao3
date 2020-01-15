<div class="{{ $form->fieldClass($field) }}{{ is_array($errors)&&isset($errors[$field])? ' field-error' : '' }}">
  <label class="label{!! $form->labelClass() ? ' ' . $form->labelClass() : ''  !!}" for="{{ $field }}">{!! $form->fieldLabel($field) !!}</label>
  {!! $form->renderInput($field, isset($data->data['type_in_form']) ? $data->data['type_in_form'] : false) !!}
</div>
