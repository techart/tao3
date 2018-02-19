@php
    $p1 = $page-3; if ($p1<1) $p1 = 1;
    $p2 = $page+3; if ($p2>$numpages) $p2 = $numpages;

    $l1 = $numpages-1;
    $l2 = $numpages;
@endphp


@if ($page>1)
  {{ Assets::setMeta('rel_canonical', call_user_func($pager_callback, 1)) }}
  @include('pager ~ link', ['number' => $page-1, 'text' => '&lt;', 'link_rel' => 'prev'])
@endif

@if ($p1>1)
  @include('pager ~ link', ['number' => 1, 'text' => 1])
 ...
@endif

@for ($i=$p1;$i<=$p2;$i++)
  @include('pager ~ link', ['number' => $i, 'text' => $i])
@endfor

@if ($p2<$l1-1)
 ...
@endif

@if ($p2<$l1)
  @include('pager ~ link', ['number' => $l1, 'text' => $l1])
@endif

@if ($p2<$l2)
  @include('pager ~ link', ['number' => $l2, 'text' => $l2])
@endif

@if ($page<$numpages)
  @include('pager ~ link', ['number' => $page+1, 'text' => '&gt;', 'link_rel' => 'next'])
@endif
