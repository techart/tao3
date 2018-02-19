<script>
    $(function() {
        var $button = $("#tao_upload_button_{{ $field->name }}");
        var $informer = $("#tao_upload_informer_{{ $field->name }}");
        var fileInput = $adminForm[0].elements['{{ $field->name }}-file'];

        fileInput.onchange = function() {
            var formData = new FormData();
            formData.append('uploadfile', fileInput.files[0])

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
                    var preview = '';
                    if (response.preview!='') {
                        preview = '<a><img src="'+response.preview+'"></a>';
                    }
                    $informer.html(preview+'<span class="filename">'+response.name+'</span> <span class="filesize">('+response.human_size+')</span>');
                }
            })
            .fail(function(response) {
                $informer.addClass('upload-error').html(response.error);
            });
        };

        $('#tao_upload_delete_{{ $field->name }}').click(function() {
            if (confirm('Вы уверены?')) {
                $('#tao_upload_hidden_{{ $field->name }}').attr('value', 'delete');
                $informer.empty();
            }
            return false;
        });
    });
</script>
