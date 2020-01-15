@if (Auth::user())
    @include('comments ~ addform')
@else
    @include('comments ~ nouser')
@endif