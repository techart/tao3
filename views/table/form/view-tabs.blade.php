<div class="navbar form-tabs-navbar">
    <div class="container-fluid">
        <div class="row">
            <ul class="nav nav-tabs">
                @foreach ($tabs as $code => $label)
                    <li class="{{ $code==$first_tab? 'active' : ''}}"><a href="#tab_{{ $code }}" data-toggle="tab">{{ $label }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

<div class="tab-content">
    @foreach ($tabs as $code => $label)
        <div id="tab_{{ $code }}" class="tab-pane {{$code==$first_tab? 'active' : ''}}">
            @include('table ~ form.view-fields', ['tab' => $code, 'tab_label' => $label])
        </div>
    @endforeach
</div>