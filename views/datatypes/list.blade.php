@extends('~layout')

@section('content')
    <h1>{{ $title }}</h1>
    @include('pager ~ site')
    @foreach($rows as $row)
        {!! $row->render(['mode' => 'teaser']) !!}
    @endforeach
    @include('pager ~ site')
@endsection
