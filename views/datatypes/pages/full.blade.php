@extends('~layout')

@section('content')
    <h1>{!! $item->field('title') !!}</h1>
    {!! $item->field('content') !!}
@endsection