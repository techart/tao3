<script>
    $(function () {
        var files = {!! $field->renderFilelistJSON($code) !!};
        var $button = $("#tao_attaches_button_{{ $field->name }}{{ $vdata['postfix'] }}");
        var $informer = $("#tao_attaches_informer_{{ $field->name }}{{ $vdata['postfix'] }}");
        var $hidden = $("#tao_attaches_hidden_{{ $field->name }}{{ $vdata['postfix'] }}");
        var $filelist = $("#tao_attaches_filelist_{{ $field->name }}{{ $vdata['postfix'] }}");

        @if ($field->isSortable())
            @bottomScript('/tao/scripts/jquery-ui.min.js')
            @style('/tao/styles/jquery-ui.css')
            $filelist.sortable({
                update: function () {
                    var items = $(this).sortable('toArray');
                    var newfiles = {};
                    $.each(items, function (key, data) {
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

        $adminForm[0].elements['{{ $field->name }}{{ $vdata['postfix'] }}-files'].onchange = function () {
            var formData = new FormData();
            var fileList = $adminForm[0].elements['{{ $field->name }}{{ $vdata['postfix'] }}-files'].files;
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
            .done(function (response) {
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
            .fail(function (response) {
                var errorMessage;
                switch (response.status) {

                    case 400:
                        errorMessage = 'Неправильный запрос на загрузку вложения.';
                        break;

                    case 401:
                        errorMessage = 'Адрес загрузки вложения требует авторизации.';
                        break;

                    case 403:
                        errorMessage = 'Адрес загрузки вложения закрыт для доступа.';
                        break;

                    case 404:
                        errorMessage = 'Не найден адрес загрузки вложения.';
                        break;

                    case 405:
                        errorMessage = 'Используемый метод загрузки вложения запрещён.';
                        break;

                    case 413:
                        errorMessage = 'Файл вложения слишком большой.';
                        break;

                    case 500:
                        errorMessage = 'На сервере произошла ошибка.';
                        break;

                    case 501:
                        errorMessage = 'Используется неизвестный серверу метод загрузки вложения.';
                        break;

                    case 502:
                        errorMessage = 'Некому принять запрос на загрузку вложения.';
                        break;

                    case 504:
                        errorMessage = 'Не смогли дождаться ответа обработчика загрузки вложения.';
                        break;

                    default:
                        errorMessage = 'При загрузке вложения произошла ошибка.';

                }
                if ('' !== errorMessage) {
                    $informer.addClass('upload-error').html([errorMessage, ' (Статус ', response.status.toString(), ')'].join(''));
                }
            });
        };
    });
</script>
