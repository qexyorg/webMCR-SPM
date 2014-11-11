Static Pages Module for webMCR

REQUIRED - https://github.com/qexyorg/webMCR-API

1. Upload files from "upload" folder in your webMCR directory

2. Go to http://yoursite.com/go/statics/

-------------------------------------------------------------

Следующие переменные доступны в файле "page-full.html"

$data['ID'] - Числовой идентификатор страницы

$data['OP'] - Строковый идентификатор страницы

$data['TITLE'] - Название страницы

$data['TEXT'] - Текст страницы

$data['AUTHOR_ID'] - Идентификатор создателя страницы

$data['UPDATER_ID'] - Идентификатор последнего обновляющего страницы

$data['AUTHOR'] - Логин создателя страницы

$data['UPDATER'] - Логин обновляющего страницы

$data['CREATED'] - Дата создания страницы

$data['UPDATED'] - Дата обновления страницы

$data['DATA'] - JSON Содержимое обновляемой информации
