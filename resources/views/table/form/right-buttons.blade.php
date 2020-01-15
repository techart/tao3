@section('right_buttons')
    <a href="{!! url($list_url) !!}" class="btn btn-info"><i class="icon-list-alt icon-white"></i> {{ $item->adminReturnToListText() }}</a>
@endsection