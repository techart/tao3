@extends('~layout')

@include('table ~ form.header')
@include('table ~ form.right-buttons')

@section('content')
	@if($tabs)
		@include('table ~ form.view-tabs')
	@else
		@include('table ~ form.view-fields', [ 'tab' => '#', 'tab_label' => '#'])
	@endif
@endsection

