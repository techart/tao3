@if (isset($main) && isset($main_url))
    <a href="{!! $main_url !!}">{!! $main !!}</a>
@endif

@foreach($links as $link)
    @if (isset($delimiter))
        {!! $delimiter !!}
    @endif
    @include('navigation ~ link')
@endforeach