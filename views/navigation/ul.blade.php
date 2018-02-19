<ul {!! $args !!}>
@foreach ($links as $link)
    @if($link->checkAccess())
        @if ($link->withDivider())
            <li class="divider"></li>
        @endif
        <li>@include('navigation ~ link')</li>
    @endif
@endforeach
</ul>