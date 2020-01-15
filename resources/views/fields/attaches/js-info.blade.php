@if ($field->withInfo())
    @bottomScript('/tao/scripts/jquery-ui.min.js')
    @style('/tao/styles/jquery-ui.css')
    $edit = $('<a>').addClass('edit').attr('href', 'javascript:void(0)').html('&nbsp;').click(function() {
        var $form = $('<div>').addClass('tao-fields-attaches__iform');
        @foreach($field->infoFields() as $name => $data)
            var ${{ $field->infoFieldId($name) }} = $('<div>').addClass('field');
            var ${{ $field->infoFieldId($name) }}_label = $('<label>').text('{!! $data['label'] !!}').appendTo(${{ $field->infoFieldId($name) }});
            var ${{ $field->infoFieldId($name) }}_container = $('<div>').addClass('input').appendTo(${{ $field->infoFieldId($name) }});
            var ${{ $field->infoFieldId($name) }}_input =
            
            @if ($data['type'] == 'checkbox')
                $('<input>').attr('type', 'checkbox').attr('id', '{{ $field->infoFieldId($name) }}_input').addClass('cb')
            @elseif ($data['type'] == 'date')
                $('<input>').attr('type', 'text').attr('id', '{{ $field->infoFieldId($name) }}_input').datepicker({dateFormat: "dd.mm.yy"}).val(files[key].info.{{ $name }})
            @elseif ($data['type'] == 'text')
                $('<textarea>').attr('id', '{{ $field->infoFieldId($name) }}_input').addClass('text').val(files[key].info.{{ $name }})
            @else
                $('<input>').attr('type', 'text').attr('id', '{{ $field->infoFieldId($name) }}_input').addClass('text').val(files[key].info.{{ $name }})
            @endif
            
            .appendTo(${{ $field->infoFieldId($name) }}_container);
            
            @if ($data['type'] == 'checkbox')
                if (files[key].info.{{ $name }} == 1) {
                    ${{ $field->infoFieldId($name) }}_input.attr('checked', 1);
                }
            @endif
            
            $form.append(${{ $field->infoFieldId($name) }});

        @endforeach
        $form.dialog({
            modal: true,
            width: 'auto',
            title: 'Доп. информация',
            close: function() {
                $form.remove();
            },
            buttons: {
                OK: function() {
                    var values = {};
                    @foreach($field->infoFields() as $name => $data)
                        @if ($data['type'] == 'checkbox')
                            var value = ${{ $field->infoFieldId($name) }}_input.prop('checked')? 1 : 0;
                        @else
                            var value = ${{ $field->infoFieldId($name) }}_input.val();
                        @endif
                        values.{{ $name }} = value;
                    @endforeach
                    files[key].info = values;
                    $(this).dialog("close");
                    renderFileList();
                },
                Отмена: function() {
                    $(this).dialog("close");
                }
            }
        });
    });
@endif