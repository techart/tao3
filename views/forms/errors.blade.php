<ul class="tao-form-errors tao-form-errors-{{ $form->getDatatype() }}">
  @foreach($errors as $field => $error)
    <li class="field-{{ $field }}">{{ $error }}</li>
  @endforeach
</ul>