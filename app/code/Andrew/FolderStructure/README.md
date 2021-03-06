##Папки и файлы в Magento 2

###Основные файлы и папки платформы
- папка app - хранит все конфигурации, темы, модули
- папка bin - содержит CLI-комманды. Одна из них - magento - основная команда для создания изменений в приложении. Это чистка кэша, инейбл/дизейбл модуля, компиляции контейнера зависмостей и т.д.
- папка dev - содержит тесты, который идут вместе с Магентой, а также тулзы, например, Grunt и другие
- папка generated - содержит автосгенерированные классы, с которыми работает аппликейшен.
- папка lib - содержит internal и web папки. web содержит 3-уровня аппликейшены - jQuery, KnockoutJS, RequireJS, а также файлы - CSS/LESS.
- папка phpserver - содержит код простого PHP-сервера, который может быть использован для разработки.
- папка pub - содержит 4 файла для входа в приложения, static и media директории для загрузки статических файлов по запросу браузера.
- папка setup - содержит скрипты для установки и апдейта аппликейшена на базе Магента 2
- папка var - содержит кэш и файлы сессии приложения, логи.
- vendor - основная папка для установки сторонних модулей


###Основные файлы и папки модуля
Папки и файлы модуля в Magento 2 могут быть размещены в 2-х каталогах:

```
app/code/<VendorName>/<ModuleName>/
vendor/<vendor-name>/<module-name>/
```

VendorName — это, в основном, название компании, которая разработала модуль. Также это может быть и, например, никнейм программиста, который создал данный модуль. В переводе с английского «vendor» означает «поставщик», что должно явно идентифицировать создателя модуля. Если вы разрабатываете модуль, используйте для этого уникальное значение.
Если модуль разрабатывается вами или вашей компанией, его файлы размещаются в папке app/code.
Если вы устанавливаете модуль через Composer, его файлы размещаются в папке vendor. Также здесь размещаются модули ядра платформы Magento - в папке vendor/magento.


Краткое описание назначения файлов и каталогов в модуле Magento 2:
```
├── Api // PHP классы для REST API;
├── Block // PHP классы, которые отвечают за View по MVC. Методы, которые используются в .phtml шаблонах;
├── Controller // эти файлы отвечают за соединение модулей с внешним миром, например, при изменении URL-адреса;
├── Cron // PHP классы, которые обслуживают методы, связанные с задачами Cron (задания по расписанию);
├── Helper // содержит файлы, которые отвечают за выполнение общих (вспомогательных) задач для объектов и переменных;
├── Model // PHP классы, которые отвечают за Module по MVC;
├── Observer // PHP классы Обсерверов (слушателей событий);
├── Plugin // PHP классы перехватчиков (до, после и вокруг);
├── Setup // содержит файлы, необходимые для внесения изменений в базу данных, то есть создание таблиц, полей или других записей, необходимых для выполнения работы модуля;
├── UI // PHP классы, которые отвечают за роботу с UI-компонентами;
├── etc // папка содержит конфигурационные файлы модуля;
│    ├── adminhtml // конфигурационные файлы, которые затрагивают только админпанель сайта;
│    │     ├── di.xml  // настройки плагинов, виртуальных типов, переписывание моделей;
│    │     ├── menu.xml  // отвечает за построение меню в админпанели;
│    │     ├── routers.xml  //настройки маршрутов;
│    │     ├── system.xml  // построение страницы настроек в разделе Stores > Configuration;
│    │     └── events.xml  //настройки Обсервера (слушателей событий);
│    │
│    ├── frontend // конфигурационные файлы, относящиеся к фронт-энду сайта;
│    │     ├── di.xml
│    │     ├── events.xml
│    │     ├── routers.xml
│    │     └── events.xml
│    │
│    ├── acl.xml  // настройки разграничения прав в админпанели;
│    ├── config.xml  // содержит дефолтные значения для полей настроек в разделе Stores > Configuration;
│    ├──  crontab.xml // файл для настройки задач Cron (задач по расписанию);
│    ├── di.xml
│    ├── webapi.xml // здесь производится настройка REST API;
│    └── widget.xml // файл с настройками для виджетов;
│
├── i18n // папка с CSV-файлами, которые отвечают за локализацию (переводы);
│    ├── en_US.csv
│    └── fr_FR.csv
│
├── view // здесь содержатся файлы, которые придают облик интернет-магазину: макеты (layout), .phtml шаблоны, js файлы, стили css, простой html, картинки и шрифты;
│    ├── adminhtml // медиа-файлы, которые придают облик бэкенду (админпанели);
│    ├── frontend // ресурсы, предоставляющие внешний вид фронт-энду;
│    │    └── layout // xml-файлы макета;
│    │    └── templates // phtml-файлы шаблона;
│    │    └── web // статические файлы;
│    │         └── css
│    │         └── images
│    │         └── js
│    │
│    └── base // дефолтные ресурсы, которые придают облик и фронт-энду, и бэкенду;
│
├── LICENSE.txt  // файл, содержащий условия лицензии, по которой предоставляется текущий модуль;
├── README.md  // техническое описание всего того, что умеет модуль (функциональности);
├── composer.json // с помощью этого файла модуль устанавливается через Composer;
└── registration.php // файл, с помощью которого модуль регистрируется в платформе Magento 2;
```