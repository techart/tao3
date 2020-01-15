@foreach($meta as $name => $value)
    @if($name == 'title')
        <title>{{ $value }}</title>
    @elseif (preg_match('{^rel_(.+)$}', $name, $m))
        <link rel="{{ $m[1] }}" href="{!! $value !!}">
    @else
        <meta name="{{ $name }}" content="{{ $value }}">
    @endif
@endforeach