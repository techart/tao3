<script>
    $(function () {
        var $button = $("#tao_upload_button_{{ $field->name }}");
        var $informer = $("#tao_upload_informer_{{ $field->name }}");
        var fileInput = $adminForm[0].elements['{{ $field->name }}-file'];

        fileInput.onchange = function () {
            var formData = new FormData();
            formData.append('uploadfile', fileInput.files[0])

            $.ajax({
                url: '{!! $field->uploadUrl() !!}',
                data: formData,
                type: 'POST',
                contentType: false,
                processData: false,
                dataType: 'json'
            })
            .done(function (response) {
                $informer.removeClass('upload-progress').empty();
                if (response.error) {
                    $informer.addClass('upload-error').html(response.error);
                } else {
                    var preview = '';
                    if (response.preview != '') {
                        preview = '<a><img src="' + response.preview + '"></a>';
                    }
                    $informer.html(preview + '<span class="filename">' + response.name + '</span> <span class="filesize">(' + response.human_size + ')</span>');
                }
            })
            .fail(function (response) {
                var errorMessage;
                switch (response.status) {

                    case 400:
                        errorMessage = 'Неправильный запрос на загрузку файла.';
                        break;

                    case 401:
                        errorMessage = 'Адрес загрузки файла требует авторизации.';
                        break;

                    case 403:
                        errorMessage = 'Адрес загрузки файла закрыт для доступа.';
                        break;

                    case 404:
                        errorMessage = 'Не найден адрес загрузки файла.';
                        break;

                    case 405:
                        errorMessage = 'Используемый метод загрузки файла запрещён.';
                        break;

                    case 413:
                        errorMessage = 'Загружаемый файл слишком большой.';
                        break;

                    case 500:
                        errorMessage = 'На сервере произошла ошибка.';
                        break;

                    case 501:
                        errorMessage = 'Используется неизвестный серверу метод загрузки файла.';
                        break;

                    case 502:
                        errorMessage = 'Некому принять запрос на загрузку файла.';
                        break;

                    case 504:
                        errorMessage = 'Не смогли дождаться ответа обработчика загрузки файла.';
                        break;

                    default:
                        errorMessage = 'При загрузке файла произошла ошибка.';

                }
                if ('' !== errorMessage) {
                    $informer.addClass('upload-error').html([errorMessage, ' (Статус ', response.status.toString(), ')'].join(''));
                }
            });
        };

        $('#tao_upload_delete_{{ $field->name }}').click(function () {
            if (confirm('Вы уверены?')) {
                $('#tao_upload_hidden_{{ $field->name }}').attr('value', 'delete');
                $informer.empty();
            }
            return false;
        });
    });
</script>
