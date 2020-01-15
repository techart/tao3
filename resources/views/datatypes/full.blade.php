@extends('~layout')

@section('content')
    <h1>{!! $item->field('title') !!}</h1>
    <p>This is a full page for <b>{{ $item->getDatatype() }}</b> item</p>
@endsection