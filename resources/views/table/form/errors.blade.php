@if ($errors)
    <div class="admin-errors alert alert-danger">
        <h3>Ошибки</h3>
        <ul>
          @foreach($errors as $field => $error)
            <li class="admin-errors admin-errors--{{ $field }}">{{ $error }}</li>
          @endforeach
        </ul>
    </div>
@endif