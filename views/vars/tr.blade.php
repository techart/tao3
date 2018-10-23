<tr>
	<td>{{ $var }}</td>
	<td>{!! $vparams['description'] !!}</td>
	<td>{!! \TAO::vars($var)->renderForAdminList() !!}</td>
	<td class="actions">
		@if (\TAO::vars($var)->accessEdit(auth()->user()))
		<a class="btn btn-primary" href="{{ url($controller->actionUrl('edit', ['id' => $var ])) }}"><i class="icon-pencil icon-white"></i></a>
		@endif
	</td>
</tr>