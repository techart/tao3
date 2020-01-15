        function renderFileList() {
            $filelist.empty();
            var count = 0;
            $.each(files, function (key, data) {
                var $name;
                var $edit = '';
                
                $del = $('<a>')
                    .addClass('delete')
                    .attr('href', 'javascript:void(0)')
                    .html('&nbsp;')
                    .click(function() {
                        if (confirm('Вы уверены?')) {
                            deleteFromFileList(key);
                        }
                    })
                ;
                
                if (data.new) {
                    $name = $('<span>').addClass('file-name').text(data.name);
                } else {
                    $name = $('<a>').addClass('file-name').attr('href', data.url).text(data.name);
                }
                
                @include("fields ~ attaches.{$field->templateEditInfoJS()}")
                @include("fields ~ attaches.{$field->templateEntryJS()}")
                
                $filelist.append($entry);
                count++;
            });
            if (count==0) {
              $filelist.append('<div class="message-empty">Нет файлов</div>');
            }
            $hidden.val(JSON.stringify(files));
        }
