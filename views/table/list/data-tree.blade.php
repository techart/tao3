<table class="table table-striped table-bordered dataTable">

  @if(count($fields)>0)
    <thead>
        <tr role="row">
        
            @foreach ($fieldsBefore as $name => $field)
                @include('table ~ list.th')
            @endforeach
            
            <th>&nbsp;</th>
    
            @foreach ($fieldsAfter as $name => $field)
                @include('table ~ list.th')
            @endforeach
        
            @if ($with_row_actions)
                <th>&nbsp;</th>
            @endif
        
        </tr>
    </thead>
  @endif
    
  <tbody>
    @foreach ($tree as $row)
        @include('table ~ list.branch', ['level' => 0, 'row' => $row])
    @endforeach
  </tbody>
    
</table>