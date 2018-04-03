@extends('~layout')

@include('table.list.setup-public')

@include('table ~ list.header')
@include('table ~ list.right-buttons')
@include('table ~ list.filter')

@section('content')
	<div class="b-admin-table">
		
		<div class="b-admin-table-top">
			<h1 class="b-admin-table-top__header">@yield('h1')</h1>
			<div class="b-admin-table-top__buttons">@yield('right_buttons')</div>
		</div>
		
		@include('table ~ list.navigator-top')
		
		<div class="block-simple">
			<div class="block-content">
				@if ($count == 0)
					@include('table ~ list.empty')
				@else
					@include('table ~ list.before-data')
					@include('table ~ list.data-table')
					@include('table ~ list.after-data')
				@endif
			</div>
		</div>
		
		@include ('table ~ list.navigator-bottom')
	</div>   
@endsection