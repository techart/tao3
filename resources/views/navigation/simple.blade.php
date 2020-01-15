@foreach($links as $link)
    @if($link->checkAccess())
        @include('navigation ~ link')
    @endif
@endforeach