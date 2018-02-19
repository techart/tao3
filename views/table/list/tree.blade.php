@extends('~layout')

@include('table ~ list.header')
@include('table ~ list.right-buttons')

@section('content')

    <div class="block-simple">
        <div class="block-content">
            @if ($count == 0)
                @include('table ~ list.empty')
            @else
                @include('table ~ list.before-data')
                @include('table ~ list.data-tree')
                @include('table ~ list.after-data')
           @endif
       </div>
    </div>

@endsection