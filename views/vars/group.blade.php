<div class="block-simple">
    <div class="block-content">
        @if ($group['title'])
            <h2>{!! $group['title'] !!}</h2>
        @endif
        
        <table class="table table-striped table-bordered dataTable">
            <thead>
                <tr role="row">
                    <th>Название</th>
                    <th>Описание</th>
                    <th>Значение</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                @foreach($group['vars'] as $var => $vparams)
                    @include('vars ~ tr')
                @endforeach
            </tbody>
        </table>
    </div>
</div>        