Добрый день!

Вы заполнили форму "{{ $name  }}" на сайте {{ config('app.name') }}.

Данные формы:
@foreach ($data as $item)
{{ $item['name'] }}: {{ $item['value'] }}
@endforeach
