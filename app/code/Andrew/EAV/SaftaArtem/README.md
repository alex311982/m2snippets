#EAV
Entity Atribute Valuе - это дизайн паттерн

Основной плюшкой является эффективное использование пространства БД в тех случаях, когда возможное количество различных атрибутов (свойств и параметров), которые могут быть использованы для описания сущностей, является широким, но количество атрибутов, которое на самом деле относится к отдельному объекту является относительно небольшим.
Основные entity в magento 2 eav схеме:
- Категории
- Продукты
- Кастомеры

`extension attributes` не относятся к eav атрибутам

#Custom и System атрибуты
Custom атрибуты добавляется с помощью сетап скриптов или можно добавить атрибут из админки для продукта

Для того чтобы создать кастомный атрибут нам необходимо создать сетап скрипт.
За создание отвечает `Magento/Eav/Setup/EavSetup` класс.
Если мы хотим чтобы наш модуль поддерживал более старые версии magento нам необходимо использовать `factory` для `EavSetup`,
Дальше добавляем атрибут код в переменую `$attributeCode` и используем `$eavSetup->addAttribute()`, который принимает три аргумента.

- `ProductAttributeInterface::ENTITY_TYPE_CODE`
- `$attributeCode`
- массив с параметрами, который характерезиует поведение атрибута

Для того чтобы определить все возможные параметры нобходимо заглянуть в мапперы
Есть мапперы для для продукта, категории, кастомера, конфигурабл продукта.

Путь к мапперинтерфейсу
`\Magento\Eav\Model\Entity\Setup\PropertyMapperInterface`

#Eav Mapper
`\Magento\Eav\Model\Entity\Setup\PropertyMapper`

записывается в `eav_attribute`

[
 - `backend` - это класс который позволяет указать крад хуки (beforeSave, afterLoad ...)
 - `type` - по дефолту varchar 255  cимволов
 - `table` - этот параметр может быть использован, если значение атрибута должно быть сохранено в специфическую таблицу. Использование (если есть необходимость добавить индекс)
 - `frontend` - frontend_model обеспечивает работу метода getValue, которые может быть использован для подготовки значения атрибута на стор фронте
 - `input` - по дефолту text
 - `label` - название нашего атрибута
 - `fronted_class` - класс нашего инпута. Будет использован для кастомной валидации и стилизации
 - `source` - это класс который обеспечивает значения для списка опции в мультиселекте и селекте
 - `required` - тригер который сигнализирует обязателен атрибут или нет (по умолчанию 0)
 - `user_defined` - с помощью этого тригера мадженто определяет это атрибут кастомный или системный (по умолчаню 0)
 - `default` - значение по умолчанию.
 - `unique`  - значение должно быть уникальным
 - `note` - будет рендерится под инпут полем
 - `is_global` - здесь указываем к какой части скоупа будет относиться наш атрибут (SCOPE_STORE = 0,SCOPE_GLOBAL = 1, SCOPE_WEBSITE = 2 )

]

#Catalog Mapper

`\Magento\Catalog\Model\ResourceModel\Setup\PropertyMapper`

записывается `catalog_eav_attribute`

[
 - `input_renderer` - используется для определения кастомного ренедрера для нашего инпута в админке
- `is_global` - дубликат из eav модули, характерезует скоуп
- `visible` - характерезует отображение нашего атрибута в админке. Если поставить значение 0. Подразумевается что атрибутом будем управлять программно
- `searchable` - кастомер сможет искать с фронта по значению нашего атрибута
- `filterable` - атрибут будет доступен для layer navagation на фронте (работает для select и multiselect, которые имеют integer значение)
- `comparable` - атрибут будет для сравнения в Compare List Product. Атрибут будет отображен
- `visible_on_front` - атрибут будет отображен на фронте в Additional Information
- `wysiwyg_enabled` - это касаеться textarea в админке. Если включен то будет,  то в textarea можно редактировать html добавлять картинки...
- `visible_in_advance_search` - будет ли искаться по этому атрибуту в advance search
- `filterable_in_search`_- атрибут будет доступен на search_result странице
- `used_in_product_listing` - атрибут будет добавлен для Product Listing страницы
- `used_for_sort_by` - атрибут будет доступен для сортировки на Product Listing странице.
- `apply_to` - масив который хранит доступность атрибут для  разных типов продуктов
- `position` - используется для сортировки в Layer Navigation на фронте
- `used_for_promo_rules` - будет ли доступен атрибут для прайс рулов и чекаут прайс рулов
- `is_used_in_grid` - отбражение на админ гриде
- `is_visible_in_grid` - должен ли отображаться по умолчанию в админ гриде
- `is_filterable_in_grid` - можно ли фильтровать по этому атрибуту в админке

]

Также можно добавить параметры для добавления нашего атрибута в определенный атрибут сет (применить его ко все атрибут сетам) и добавить в опреденную группу атрибутов

[
 - `group` - указываем группу, в которой будет находится наш атрибут
 - `sort_order` - порядок отображение в группе атрибутов в админке (по умолчанию 0)

]

Полчение дефолтного названия группы атрибута

```
$setId = $eavSetup->getDefaultAttributeSetId(ProductAttributeInterface::ENTITY_TYPE_CODE);
$groupId = $eavSetup->getDefaultAttributeGroupId(ProductAttributeInterface::ENTITY_TYPE_CODE, $setId);
$groupName = $eav->getAttributeGroup(ProductAttributeInterface::ENTITY_TYPE_CODE, $setId, $groupId, 'attribute_group_name');
```



#Category Attribute

Для категории нам необходимо создать и сетап скрипт и добавить `category_form.xml`, в котором нам необходимо добавить наш созданный атрибут

#Customer Attribute

`\Magento\Customer\Model\ResourceModel\Setup\PropertyMapper`

[
 - `visible` - тригер для отображения атрибута в админ панели
 - `system` - указывает к каким атрибутам относится наш атрибут к системным или кастомным
 - `input_filter` - перед сохранением будет удалять по фильтру например striptags будет удалять теги
 - `multiline_count` - относится к street атрибуту, то есть сколько полей будет занимать атрибут.
 - `validate_rules` - можно добавить правила для валидации, которые будут применяться преед сохранением атрибута. Это json c правилами
 - `position` - позиция нашего атрибута в форме
 - `is_used_in_grid` - параметры относятся к отображению в админ гриде
 - `is_visible_in_grid` - параметры относятся к отображению в админ гриде
 - `is_filterable_in_grid` - параметры относятся к отображению в админ гриде
 - `is_searchable_in_grid` - параметры относятся к отображению в админ гриде
]

В мадженто для кастомера один атрибут сет, но нам необходимо его задать

````
$eavSetup->addAttributeToSet(
    CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
    CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
    null,
    $attributeCode
);
````

Также для атрибутов необходимо указывать в `module.xml` после какого модуля грузить наш, то есть для кастоер атрибута надо добавить Magento_Customer в `module.xml`


Для кастомер атрибутов есть еще один важный момент. Есть таблица `customer_form_attribute` в ней храняться все формы. Чтобы атрибут добавлися в определнную форму нам необходимо добавить его в форму

Для этого добавляем в наш конструктор `Magento\Eav\Model\Config` класс

````
$attribute = $this->eavConfig->getAttribute(
    CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
    $attributeCode
);
````

````
$attribute->setData('used_in_forms', [
    'adminhtml_customer',
    'customer_accout_create',
    'customer_account_edit'
]);
$attribute->getResource()->save(attribute);
````


#Сustomer Address Attribute

Используем те же маперы что и для кастомер атрибута

Также необходимо задать атрибут сет

````
$eavConfig->addAttributeToSet(
    AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
    AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
    null,
    $attributeCode
);
````

Также надо добавить в формы

````
$attribute->setData('used_in_forms', [
    'adminhtml_customer_address',
    'customer_address_edit',
    'customer_register_address'
]);
$attribute->getResource()->save(attribute);
````



