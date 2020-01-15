@extends('~layout')

@section('content')
    <h1>{{ $title }}</h1>

    @empty($rows)
        @isset($empty_block)
            @include($empty_block)
        @else
            @include('empty-list')
        @endisset
    @else
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
    @endempty
    
@endsection
