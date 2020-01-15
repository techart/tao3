@extends('~layout')

@include('table.form.setup-public')
@include('table ~ form.header')
@include('table ~ form.right-buttons')

@section('content')

    <div class="b-admin-table">

        <div class="b-admin-table-top">
            <h1 class="b-admin-table-top__header">@yield('h1')</h1>
            <div class="b-admin-table-top__buttons">@yield('right_buttons')</div>
        </div>

        @include('table ~ form.before-add')
        @include('table ~ form.form')
        @include('table ~ form.after-add')
        
    </div>
    
@endsection

