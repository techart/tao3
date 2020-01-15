@extends('~layout')

@section('h1'){!! $title !!}@endsection

@section('content')
    @include('table ~ form.form')
@endsection

