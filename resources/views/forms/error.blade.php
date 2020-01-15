@extends('~layout')

@section('content')

    <h1>{{ $form->typeTitle() }}</h1>
    @include($form->templateErrors($form_context))
    {!! $form->renderForm($form_template, $form_context)!!}
@endsection
