##Declarative Shema
Mgento 2.3 предоставила Declarative Schema способ для создания структуры базы данных. Этот способ онсован на xml конфигурации в файле `db_schema.xml`

Setup cкрипты по-прежнему работают.
 
При использовать Declarative Schema нет необходимости указывать версию в `module.xml`.

`db_schema.xml` мерджится также как и вся xml конфигурация.
Для того чтобы нам изменить таблицу или колонки нам необходимо изменить `db_schema.xml` и запустить `setup:upgrade`
#####Integer колонки
![Alt text](./integerTypes.png?raw=true "Priority")
#####Дефолтные значения padding для разных типов integer
![Alt text](./padding.png?raw=true "padding")

Атрибут `unsigned` разрешает либо запрещает отрицательные значения.

`identity="true"` говорит что поле будет `primary key`
Для того чтобы задекларировать наш primary key необходимо добавить ноду <constraint>
c `type="primary"` `referenceId="PRIMARY"`
и добавить нашу колонку `<column name="id"/>`
#####Текстовые колонки
![Alt text](./textTypes.png?raw=true "textTypes")

#####Binary Types
![Alt text](./binaryTypes.png?raw=true "binaryTypes")

Decimal типы `decimal` `float` `double`
У этого типа есть такие атрибуты

`precision` -  максимально число цифр во всем числе

`scale` - это количество цирф после запятой

#####Временные типы
![Alt text](./dateType.png?raw=true "dateType")


#####Дефолтные значения для временых типов
![Alt text](./temporalTypes.png?raw=true "temporalTypes")

####Внешний ключи
Для создания используется нода `constraint` c типом `foreign` 
Ещё указываем такие атрибуты `referenceId`,a для колонки `referenceTable` `referenceColumn`
и атрибут `onDelete`

При добавление `db_schema.xml` генерерируется `db_schema_whitelist.json`
Этот позволяет сушествование обоих путей создания таблиц и колонок.

`db_schema_whitelist.json` можно сгенерировать с помощью команды 
`bin/magento setup:db-declaration:generate-whitelist`
как параметр `--module=Module_Name` можно указать модуль в котором нужно сгенерировать `whitelist`
Каждый раз при изменении `db_schema.xml` нам необходимо генерировать `whitelist`

Команда `bin/magento setup:upgrade --dry-run=1` предназначена для того чтобы запустить изменения только из `db_schema.xml`
игнорируя старый способ Setup скриптов.

А в `var/log/dry-run-installation.log` пишутся выполненые запросы

Каждый раз этот файл будет перезаписан.

Дата патчи это php классы которые модфицируют данные бд.
Эти патчи выполняются один раз
и записываются в таблицу `patch_list`
Хранятся  в директории `Setup/Patch/Data/`.
При создании необходимо имплементировать три метода
`getDependencies`, `getAliases`, `apply`

`apply` здесь основная логика

`getDependencies` указываем от каких патчей зависит наша таблица. Массив с класссами патчей

Чтобы добавить наши данные в таблицу нам необходимо запустить команду `bin/magento setup:upgrade`

####Declarative Schema Деструктивные операции

- Удаление колонки или таблицы
- Изменеие length для колонки
- Изменение атрибута precision для decimal или float колонки
- Изменения типа колонки

Если запустить команду `setup:upgrade` без `--safe-mode=1`, то все данные удалятся
после апгрейда в папке `var/declarative_dumps_csv/` создаться `csv` файл с данными, которые можно будет вернуть

#####Чтобы вернуть данные необходимо
- Откатить изменения для db_schema.xml
- Запустить команду bin/magento setup:upgrade --data-restore=1 --safte-mode=1

#####Удаление модуля:
- Отключаем модуль `bin/magento module:disabele Module_Name`
- Запускаем bin/magento setup:upgrade
- Удаляем модуль









