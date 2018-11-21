@component('mail::message')
Добрый день!
------------

Вы заполнили форму "{{ $name  }}" на сайте {{ config('app.name') }}.

Данные формы:
@component('mail::table')
| Поле                | Значение             |
| ------------------- | -------------------- |
    @foreach ($data as $item)
| {{ $item['name'] }} | {{ $item['value'] }} |
    @endforeach
@endcomponent

@endcomponent