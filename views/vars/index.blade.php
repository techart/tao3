@extends('~layout')

{{ Assets::setMeta('title', $title) }}
@section('h1'){{ $title }}@endsection

@section('content')
    @if (count($varGroups)==0)
        <p>Нет настроек, доступных для редактирования</p>
    @endif
    @foreach($varGroups as $group)
        @include('vars ~ group')
    @endforeach
@endsection