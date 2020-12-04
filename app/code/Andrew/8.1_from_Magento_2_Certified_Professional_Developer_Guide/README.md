# 8.1 Применение квот, часть квоты, и правил корзины на чекауте

Квота содержит данные для создания ордера. Это временная информация и может быть изменена пользователем.
После создания квоты нельзя измененить данный квоты. Для чего Магента содает квоты:

- для сохранения инфо о продуктах в шопинг-карте вместе с инфой про цены, количество и опции
- для сохранения выбраных билинг и шипинг адресов
- для сохранения цен шипинг
- для сохранения промежуточных итоговых цен, дополнительных цен (за доставку, налоги, ...) и купоны для получения общей стоимости
- для сохранения выбранного метода оплаты

Таблицы для сохранения квот в базе данных:

- quote
- quote_address
- quote_address_item
- quote_id_mask
- quote_item
- quote_item_option
- quote_payment
- quote_shipping_rate

Используемая модель для работы с квотами - Magento\Quote\Model\Quote.
Используемая модель для работы с адрессными квотами - Magento\Quote\Model\Quote\Address.
Квота обычно содержит 2 адреса (биллинг и шиппинг), но может содержать и более если несколько адресов доставки или нет ни одного.
Если квота не будет содержать адрес, то общая цена не будет зависить от прайс-рулов конкретной страны. Если продукты виртуальны в квоте, то тгда также адрес доставки не будет учитываться
и будет вызван метод isVirtual(), для расчета общей цены, и только билинг-адрес будет браться во внимание. Для продуктов квоты применяется модель - Magento\Quote\Model\Quote\Item.
Для платежа - Magento\Quote\Model\Quote\Payment. 

###Правила (рулы) на странице чекаута.

Рулы для корзины могут изменять финальную цену продуктов или цену доставки. Их можно создать сконфигурировать через админ панель. 
Можно создать купон для пересчета общей цены. Можно также создать дискаунт или бесплатный шипинг для карты. Все созданные рулы находятся по адресу Marketing->
Cart Price Rules-> Add New Rule. Можно применять рулы для: 

- комбинации аттрибутов продукта
- для определенных продуктов
- для комбинации условий
- для промежуточных условий
- общее количество продуктов
- общий вес
- платежный метод
- метод доставки
- почтовый индекс доставки
- регион адреса доставки
- штат/провинция адреса доставки
- страна доставки

Для работы с такими рулами используется модель - Magento\SalesRule\Model\Rule. С помощью ее можно программно создать рул или использовать уже существующий.
Созданные рулы могут существенно влиять на время формирования результирующей цены. Поэтому не рекомендуется иметь много активных рулов одновременно.

###Поля и методы квоты

Если установить в базе данных опцию trigger_recollect=1, то квота, сохраненная на стороне клиента, будет апдейтиться всякий раз, как цена на продукт, находящийся в квоте,
будет меняться или продукт станет недоступным. С версии 2.3 эта опция возвращается в состояние отключена после подсчета тотала для квоты.
Тоже самое при изменении CatalogRules происходит пересчет квоты. Для этой цели есть методы - Magento\Quote\Model\ResourceModel markQuotesRecollectOnCatalogRules() и markQuotesRecollect().
Квоты также расширяемы через икстеншен-аттрибуты. Например, можно зарегистрировать икстеншен через файл extension_attributes.xml:
```
<extension_attributes for="Magento\Quote\Api\Data\CartInterface">
    <attribute code="shipping_assignments"
        type="Magento\Quote\Api\Data\ShippingAssignmentInterface[]" />
</extension_attributes>
```

Используя плагины для Quote Repository можно дополнить логикой методы репозитория afterLoad(), beforeSave(), или whenever(). Квоты не используют кастомные аттрибуты, т.к. они не EAV типа.

Кастомные аттрибуты для адрессной квоты берутся из возвращаемого массива метода \Magento\Quote\Model\Quote\Address\CustomAttributeList getAttributes().
Для того чтобы имплементировать его необходимо использовать плагин.

Полезные методы Quote item:

- Magento\Quote\Model\Quote\Item checkData() вызывается после добавления продукта в корзину и апдейте опций
- Magento\Quote\Model\Quote\Item setQty() - вызывается для валидации склада
- Magento\Catalog\Model\Product\Type\AbstractType checkProductBuyState() - вызывается для проверки возможности покупки товара
- Magento\Quote\Model\Quote\Item setCustomPrice()
- Magento\Quote\Model\Quote\Item getCalculationPrice() - возвращает оригинальную цену товара до подсчета ее с учетом налогов
- Magento\Quote\Model\Quote\Item isChildrenCalculated() - если есть родительская квота и дочерняя, то проверяет все ли айтэмы подсчитаны
- Magento\Quote\Model\Quote\Item isShipSeparately() - проверяет можно ли каждый айтэм квоты отправлять отдельно или как единой посылкой
- Magento\Quote\Model\Quote\Item\Compare::compare - не добавляет нового айтэма в квоту, а по-возможности изменяет количеств уже добавленных айтэмов
- Magento\Quote\Model\Quote\Item representProduct() - сравнивает айтэмы квоты с новым продуктом, сверяет продакт id, кастомные опции
- Magento\Quote\Model\Quote\Item compareOptions() - сверяет два массива с опциями. Первый массив - прерогативный, а второй - сверяется относительно первого.

Способы проверки доступности товара на складе:

- При вызове метода Magento\Quote\Model\Quote\Item setQty() происходит проверка доступности товара на складе
- Подписавшись на ивент "sales_quote_item_qty_set_after" можно реализовать проверку доступности товара на складе
- Вызвав метод \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator::validate для проверки доступности товара на складе

Способы добавления продуктов в карту:

- Вызвав метод Magento\Quote\Model\Quote addProduct(). Если продукт не может быть добавлен, то вернет эррор.
- Вызвав метод Magento\Catalog\Model\Product\Type\AbstractType prepareForCartAdvanced() для подготовки добавления продукта в карту.
Есть более продвинутый метод Magento\Catalog\Model\Product\Type\AbstractType processMode().
- Вызвав метод Magento\Quote\Model\Quote\Item\Processor::prepare для установки количества и кастомных цен для квотовских айтэмов
- Подписавшись на событие "sales_quote_product_add_after"
- Подписавшись на события "sales_quote_save_after", "sales_quote_save_before"
- Подписавшись на событие "checkout_cart_add_product_complete"

Способы апдейта карты:

- Вызвав метод \Magento\Quote\Model\Quote updateItemUpdate()
- Подписавшись на события "sales_quote_save_after", "sales_quote_save_before"

Как можно кастомизировать процесс добавления продукта в корзину:

- Реализовав плагин для метода \Catalog\Model\Product\Type\AbstractType prepareForCartAdvanced(). Он создан для приготовления продукта к добавлению в корзину.
- Реализовав плагин для метода Magento\Quote\Model\Quote::addProduct
- Реализовав плагин для метода Magento\Quote\Model\Quote::addItem
- Подписавшись на событие "catalog_product_type_prepare_full_options" при условии полной валидации, или "catalog_product_type_prepare_lite_options" при условии частичной валидации.
- Реализовав плагин для метода Magento\Quote\Model\Quote\Item\Processor::prepare для установки количества и кастомных цен для квотовских айтэмов
- Подписавшись на событие "sales_quote_product_add_after" можно изменить значение аттрибута цены продукта
- Подписавшись на событие "sales_quote_add_item"
- В файле catalog_attributes.xml можно добавить аттрибут, который попадет в создаваемую квоту. Например:
```
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Catalog:etc/catalog_attributes.xsd">
    <group name="quote_item">
        <attribute name="sku"/>
        <attribute name="type_id"/>
        <attribute name="name"/>
        <attribute name="status"/>
        <attribute name="visibility"/>
        <attribute name="price"/>
        <attribute name="weight"/>
        <attribute name="url_path"/>
        <attribute name="url_key"/>
        <attribute name="thumbnail"/>
        <attribute name="small_image"/>
        <attribute name="tax_class_id"/>
        <attribute name="special_from_date"/>
        <attribute name="special_to_date"/>
        <attribute name="special_price"/>
        <attribute name="cost"/>
        <attribute name="gift_message_available"/>
    </group>
</config>
```

Возможные сценарии работы с квотой:

- Добавление продуктов из каталога
- Добавление продуктов из из вишлиста
- Одновременное добавление продуктов из вишлиста в корзину
- Создать ордер из админки
- Пересоздать ордер из админки
- Переконфигурация добавленного продукта путем изменения кастомных опций
- Если неавторизированный кастомер добавил товары в корзину, то при авторизации они останутся в квоте