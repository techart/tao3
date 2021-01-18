## 5.3.0

### Добавлено

- В защите форм от роботов можно сделать редирект на страницу с сообщением или отобразить blade-шаблон (@gusev)


## 5.2.1

### Исправлено

- Ошибка в роутере FSPages (@gusev)

## 5.2.0

### Добавлено

- \TAO::navigation()->exists($name) - существует ли такая навигация? (@gusev)
- В шаблонах админской формы можно переопределить поле вместе с обвязкой, а не только сам инпут (@gusev)
- Мезанизм сброса пароля (@gusev)
- Конфиг: путь до фронтенда (@gusev)
- Конфиг: файл роутов (@gusev)
- Конфиг: адрес редиректа после логина в публичной части (@gusev)

### Исправлено

- Ошибка в поле PublicUpload, не позволявшая добавлять более одного upload-поля в форму (@gusev)


## 5.1.0 (30.01.2020)

### Добавлено

- Добавлено поле типа Decimal (@kraynova)

### Исправлено

- В поле типа float теперь можно устанавливать отрицательное число (@kraynova)
- В поле типа integer теперь можно установить отрицательное число (@kraynova)
- Вместо leafo/scssphp (abandoned) используем scssphp/scssphp (@gusev)

## 5.0.1 (27.01.2020)

### Исправлено

- Если не настроено логирование, то автоматически создается конфиг. Иначе творится кошмар в storage/logs/laravel.log (@gusev)

## 5.0.0 (15.01.2020)

### Изменено

- Преход но Laravel 6.6
- Структура каталогов: views, pages, public переехали в resources

### Добавлено

- Механизм проверки урлов на уникальность (@kotov)
- Поддержка SCSS (@gusev)
- Очередь resources - возможность практически полностью сменить скин админки


## 4.2.0 (18.12.2019)

- Добавлены новые типы полей: huge_select, huge_multilink, multilink_ids, iframe (@gusev)
- В app('tao.http') добавлен метод getResponse (@qusev)
- Табличный контроллер в админке: embedded-режим (без админского лейаута) (@gusev)

### Исправлено

- Исправлена некорректная обработка конфигов для поля Html #128 (@selin)
- Исправлена ошибка приведения объекта поля к строке #129 (@selin)
- Доработана проверка формата даты Utils::dateTime у полей DateInteger и DateSQL #129 (@zabrodskij)
- При добавлении id в список полей (например, для показа в таблице) сбрасывался автоинкремент (@gusev)
- Исправлено переопределение шаблона meta (@gusev)
- Исправлена ошибка в консоли при попытке оповещения об ошибке #118 (@selin)
- Теперь учитывается аргумент $limit в хэлпере who_calls #132 (@selin)

## 4.1.0 (01.08.2019)

### Добавлено

- Исправлен цвет текста элемента radio в форме административной части #106 (@kotov)
- Поиск по полям "Исходный URL", "Дополнительный URL" в компоненте "UrlRewriter" #79 (@kotov)
- Фильтрация по полю "Тип действия" в компоненте "UrlRewriter" #81 (@kotov)
- Возможность фильтрации полей, отображаемых в письме-оповещении о новом сообщении из формы #122 (@selin)
- Формы на сайте: Возможность утсановить from для писем-оповещений #124 (@selin)
- Экспорт в CSV: добавлена возможность использовать вычисляемые поля (@gusev)

### Исправлено

- Исправлена некорректная отправка оповещений с форм с несколькими получателями #123 (@selin)
- Kernel: Исправлена регистрация route middlewares #127 (@selin)