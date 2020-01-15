@php 
$group = false;
@endphp
@foreach ($fields as $field)
    @if ($tab == '#' || $tab == $field->adminTab())
        @if ($field->adminGroupLabel() != $group)
        
            @if ($group)
                </fieldset></div>
            @endif
            
            @php
             $group = $field->adminGroupLabel();
            @endphp 
            
            @if ($group == '#' || $tab_label == $group)
                <div class="block-simple form-horizontal">
            @else
                <div class="block form-horizontal">
                    <div class="navbar navbar-inner block-header">
                        <div class="muted pull-left">{{ $group }}</div>
                    </div> 
            @endif
            <fieldset class="admin">
        @endif
    
        <div class="control-group">
            <label class="control-label" for="{{ $field->name }}">{!! $field->labelInAdminForm() !!}</label>
            <div class="controls">
                <div class="view-container">
                    {!! $field->renderForAdminView() !!}
                </div>
            </div>
        </div>
    @endif
@endforeach
@if ($group)
    </fieldset></div>
@endif
