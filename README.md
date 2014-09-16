Static Pages Module for webMCR

1. Upload files from "upload" folder in your webMCR directory

2. Open file .htaccess add strings:
RewriteRule ^go/statics/(\w+)/?$ index.php?mode=statics&do=$1 [L,NE]
RewriteRule ^go/statics/(\w+)/(\w+)/?$ index.php?mode=statics&do=$1&op=$2 [L,NE]
RewriteRule ^go/statics/(\w+)/(\w+)/(\d+)/?$ index.php?mode=statics&do=$1&op=$2&act=$3 [L,NE]
RewriteRule ^go/statics/(\w+)/page-(\d+)/?$ index.php?mode=statics&do=$1&pid=$2 [L,NE]

After:

RewriteRule ^go/([^/]+)/?$ index.php?mode=$1 [L,NE]

3. Go to http://yoursite.com/go/statics/

-------------------------------------------------------------

Следующие переменные доступны в файле "page-full.html"

$id - Числовой идентификатор страницы

$op - Строковый идентификатор страницы

$title - Название страницы

$text - Текст страницы

$author_id - Идентификатор создателя страницы

$updater_id - Идентификатор последнего обновляющего страницы

$author - Логин создателя страницы

$updater - Логин обновляющего страницы

$created - Дата создания страницы

$updated - Дата обновления страницы
