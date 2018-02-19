@extends('~layout')

@section('content')
    <h1>{{ $title }}</h1>
    
    @if ($numpages>1)
        @include('pager ~ site')
    @endif
    
    @foreach($rows as $row)
        {!! $row->render([
            'mode' => $row_mode,
        ]) !!}
    @endforeach
    
    @if ($numpages>1)
        @include('pager ~ site')
    @endif
    
@endsection
