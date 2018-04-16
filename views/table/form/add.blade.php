@extends('~layout')

@include('table ~ form.header')
@include('table ~ form.right-buttons')

@section('content')
	@include('table ~ form.errors')
    @include('table ~ form.before-add')
    @include('table ~ form.form')
    @include('table ~ form.after-add')
@endsection

