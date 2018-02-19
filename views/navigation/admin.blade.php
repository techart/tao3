<ul class="nav">
@foreach($links as $link)
    @if($link->checkAccess())
        @if(!$link->hasChilds())
            <li>@include('navigation ~ link')</li>
        @elseif($link->hasGrantedChilds())
            <li class="dropdown">
                <a href="{{ url($link->url) }}" data-toggle="dropdown" class="dropdown-toggle">{{ $link->title }} <b class="caret"></b></a>
                {!! $link->render('ul', ['args' => 'class="dropdown-menu"', 'is_admin' => true]) !!}
            </li>
        @endif
    @endif
@endforeach
</ul>