@component('mail::message')
На сайте {{ config('app.name') }} заполнена форма "{{ $name }}".

@component('mail::table')
| Поле                | Значение             |
| ------------------- | -------------------- |
@foreach ($data as $item)
| {{ $item['name'] }} | {{ $item['value'] }} |
@endforeach
@endcomponent

@endcomponent