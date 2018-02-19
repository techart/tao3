@extends('~layout')

@include('table ~ list.header')
@include('table ~ list.right-buttons')
@include('table ~ list.filter')

@section('content')
    @include('table ~ list.navigator-top')

    <div class="block-simple">
        <div class="block-content">
            @if ($count == 0)
                @include('table ~ list.empty')
            @else
                @include('table ~ list.before-data')
                @include('table ~ list.data-table')
                @include('table ~ list.after-data')
           @endif
       </div>
    </div>

    @include ('table ~ list.navigator-bottom')

@endsection