@php
    $classes = array();
    
    if ($number == $text) {
        if ($number == 1) {
            $classes[] = 'first';
        }
    
        if ($number == $numpages) {
            $classes[] = 'last';
        }
    
        if ($number == $page) {
            $classes[] = 'current';
            $classes[] = 'btn-inverse';
        }
    }
    
    $href = call_user_func($pager_callback, $number);
    
    if (isset($link_rel)) {
        Assets::setMeta("rel_{$link_rel}", $href);
    } 
    
@endphp

<a class="btn {{ implode(' ', $classes) }}" href="{!! $href !!}">{{ $text }}</a>