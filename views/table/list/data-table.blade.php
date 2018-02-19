<table class="table table-striped table-bordered dataTable">

    <thead>
        <tr role="row">
    
            @foreach ($fields as $name => $field)
                @include('table ~ list.th')
            @endforeach
        
            @if ($with_row_actions)
                <th>&nbsp;</th>
            @endif
        
        </tr>
    </thead>
    
    <tbody>
    @foreach ($rows as $row)
        <tr>
            @foreach ($fields as $name => $field)
                @include('table ~ list.td', ['field' => $row->field($name)])
            @endforeach
            
            @include('table ~ list.actions')
        </tr>
    @endforeach
    </tbody>
    
</table>