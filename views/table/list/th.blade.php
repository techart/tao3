<th{!! $field->thAttrsInAdminList() !!}>
	@if ($orderUrl = $order_fields[$field->name] ?? false)
		<a class="order" href="{{ $orderUrl }}">
	@endif
	{!! $field->labelInAdminList() !!}
	@if ($orderUrl)
		</a>
	@endif
</th>