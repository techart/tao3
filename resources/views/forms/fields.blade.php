@foreach($form->publicFields() as $field => $data)
  @include($form->templateField($field))
@endforeach
