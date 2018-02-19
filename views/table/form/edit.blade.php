@extends('~layout')

@include('table ~ form.header')
@include('table ~ form.right-buttons')

@section('content')
    @include('table ~ form.before-edit')
    @include('table ~ form.form')
    @include('table ~ form.after-edit')
@endsection

