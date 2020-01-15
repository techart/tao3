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
                    <div class="block-header">
                        <div class="text-muted pull-left">{{ $group }}</div>
                    </div> 
            @endif
            <fieldset class="admin">
        @endif
    
        <div class="form-group">
            <label class="col-sm-2 control-label" for="{{ $field->name }}">{!! $field->labelInAdminForm() !!}</label>
            <div class="col-sm-10">
                {!! $field->renderInput() !!}
                @if ($comment = $field->param(['comment_in_admin', 'comment'], false))
                <p class="comment">{!! $comment !!}</p>
                @endif
            </div>
        </div>
    @endif
@endforeach
@if ($group)
    </fieldset></div>
@endif
