#Layout

Приложение в Magento конфигурирется с помощью xml файлов.

Это позволяет гибко расширять и изменять Magento

Все xml файлы мерджатся в зависимости от имени.

Результат мёрджа кэшируется. 

Любая кофнигурация будет перезапиасана модулем который будет загружаться позже.

Ноды перезаписываются мерджаться на основе `$_isAttributes` массиве которы хранит путь к ноде.

Например для routes.xml
     
````
protected $_idAttributes = [
    '/config/router' => 'id',
    '/config/router/route' => 'id',
    '/config/router/route/module' => 'name',
];
````
Для каждого xml файла существует xsd схема,
 которая регламентурует использование имен для нод.

При выполнении этой команды `bin/magento dev:urn-catalog:generate .idea/misc.xml` сгенерируется асбсолютный путь  для xsd файлов. 
И будут подсвечиваться неправильные ноды.