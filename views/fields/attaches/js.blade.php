<script>
    $(function() {
        var files = {!! $field->renderFilelistJSON() !!};
        var $button = $("#tao_attaches_button_{{ $field->name }}");
        var $informer = $("#tao_attaches_informer_{{ $field->name }}");
        var $hidden = $("#tao_attaches_hidden_{{ $field->name }}");
        var $filelist = $("#tao_attaches_filelist_{{ $field->name }}");
        
        @if ($field->isSortable())
            @bottomScript('/tao/scripts/jquery-ui.min.js')
            @style('/tao/styles/jquery-ui.css')
            $filelist.sortable({
                update: function() {
                    var items = $(this).sortable('toArray');
                    var newfiles = {};
                    $.each(items, function(key, data) {
                        var $e = $('#' + data);
                        var key = $e.attr('data-key');
                        newfiles[key] = files[key];
                    });
                    files = newfiles;
                    renderFileList();
                }
            });
        @endif
        
        @include("fields ~ attaches.{$field->templateFilelistJS()}")
        
        function deleteFromFileList(key) {
            delete files[key];
            renderFileList();
        }

        renderFileList();

        $adminForm[0].elements['{{ $field->name }}-files'].onchange = function() {
            var formData = new FormData();
            var fileList = $adminForm[0].elements['{{ $field->name }}-files'].files;
            for (var i = 0; i < fileList.length; i++) {
                formData.append("uploadfile[]", fileList[i]);
            }

            $.ajax({
                url:  '{!! $field->uploadUrl() !!}',
                data: formData,
                type: 'POST',
                contentType: false,
                processData: false,
                dataType: 'json'

            })
            .done(function(response) {
                $informer.removeClass('upload-progress').empty();
                if (response.error) {
                    $informer.addClass('upload-error').html(response.error);
                } else {
                    if (typeof response.files !== 'undefined' && response.files.length)
                    {
                        for (i in response.files) {
                            var key = response.files[i].key;
                            files[key] = response.files[i];
                        }
                    }
                    renderFileList();
                }
            })
            .fail(function(response) {
                $informer.addClass('upload-error').html(response.error);
            });
        };
    });
</script>
