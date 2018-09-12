<ul class="nav navbar-nav">
@foreach($links as $link)
    @if($link->checkAccess())
        @if(!$link->hasChilds())
            <li>@include('navigation ~ link')</li>
        @elseif($link->hasGrantedChilds())
            <li class="dropdown">
                <a href="{{ url($link->url) }}" data-toggle="dropdown" class="dropdown-toggle" role="button" aria-haspopup="true" aria-expanded="false">{{ $link->title }} <b class="caret"></b></a>
                {!! $link->render('ul', ['args' => 'class="dropdown-menu"', 'is_admin' => true]) !!}
            </li>
        @endif
    @endif
@endforeach
</ul>