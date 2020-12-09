#Создание таблицы

Для того чтобы создать таблицу нам необходимо Создать Сетап скрип `InstallSchema`

В методе `install`, который принимает два параметра` SchemaSetupInterface $setup` и `ModuleContextInterface $context`
Далее оборачиваем наши действия в `$setup->startSetup()` и `$setup->endSetup()`
В переменую сохраняем навзание таблицы
В `$setup->getTable($tableName)` передать его назание. Чтобы быть увереным, что название проходит валидацию.
`$table = $setup->getConnection->newTable($setup->getTable($tableName));`

После этого добавляем колонки.

````
$table->addColumn(
    'id',
     \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null, 
    [
        'primary' => true, 
        'indentity' => true, 
        'unsigned' => true, 
        'nullable' => false
    ]
);
````
В `addColumn` добавляем первым параметром название колонки, вторым тип колонки, для size (для `id` `size` = `null`), четвертым параметром передаем массив с параметрами.
Для `id`  указываем что это будет `primary_key`, `identity = true`, далее указываем что значения только положительные и поле не может быть равно `null`


Для текстового поля мы указываем такие параметры
- имя колонки,
- тип Table::TYPE_TEXT
- размер 124
- массив с параметрами в котором можем указать что поле name не может равняться null. Также для текстового поля мы можем укахать default параметр например ''

Для типа TABLE::TYPE_BOOLEAN указываем такие параметры
- название колонки например `is_enabled`
- тип `TABLE::TYPE_BOOLEAN`
- размер можно `null`
- И массив с параметрам `['default' => 0]`

Для типа Decimal мы указываем такие параметры
- имя например `weighting_factor`
- тип `TABLE::TYPE_DECIMAL`
- массив с параметрами `[5,4]` `5` количество цифр, `4` это количество цифр после запятой
- также указываем что `defaul => 0`

Для временых колонок мы можем использовать два типа `DATETIME` и `TIMESTAMP`
Необходимо указать такие параметры:
- имя `created_at`
- тип `Table::TYPE_TIMESTAMP`
- размер `null`
- `['default' => TABLE::TIMESTAMP_INIT] либо ['default' => TABLE::TIMESTAMP_UPDATE]`


Добавление индексов

````
$table->addIndex(
    $setup->getIdxName($tableName, ['is_enabled']),
    ['is_enabled' ]
);
````

Добавление внешнего ключа
````
$table->addForeignKey(
    $setup->getFkName($tableName, 'store_id', 'store', '$store_id'),
    'store_id', 
    'store', 
    '$store_id', 
    Db::FK_ACTION_CASCADE
);
````
Здесь указываем поле, которое будет связано с другой таблицей указываем поле во другой таблице и удалять каскадно

Дальше вызываем `$setup->getConnection()->createTAble($table);`
