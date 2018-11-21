На сайте {{ config('app.name') }} заполнена форма "{{ $name }}".

@foreach ($data as $item)
{{ $item['name'] }}: {{ $item['value'] }}
@endforeach
