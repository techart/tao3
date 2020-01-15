@if ($with_filter)
  @section('sidebar')
     @if (!$filter_empty)
         <a href="{{ $reset_filter_url }}" class="btn btn-danger tao-reset-filter-button" title="Сбросить фильтр"><i class="icon-trash icon-white"></i></a>
     @endif
     <h2>Поиск</h2>
     <form class="tao-filter" method="post" action="{!! $filter_url !!}">
         {{ csrf_field() }}
         <fieldset>
         @foreach($filter as $name => $field)
             <div class="field-container">
                 <label for="{{ $name }}">{!! $field->labelInAdminForm() !!}</label>
                 <div class="input-container">{!! $field->renderInput() !!}</div>
             </div>
         @endforeach
         </fieldset>
         <button type="submit" class="btn btn-primary"><i class="icon-ok icon-white"></i> Искать</button>
     </form>
  @endsection
@endif