Демонстрация возможности кастомизировать продукты в M2

Типы продуктов в M2

- Simple - базовые простые продукты
- Configurable - этот тип продуктов включает в себя много простых продуктов (коллекция вариаций продуктов с разными опциями). Каждый симпл имеет свой собственный ску. Пример: футболки разных размеров и цветов
- Grouped - это группа симплов или виртуальных продуктов, которые имеют одинаковые характеристики или связаны друг с другом. Позволяет сэкономить больше чем по отдельности и увеличивает продажи. Пример: тенисные мячи можно купить 10 дешевле
- Virtual - основное различие от видимых и физических продуктов в том, что virtual продукт это тип услуги, членство, гарантия, подписка. Не требует отправителей и доставки.
- Bundle -включают в себя простые и виртуальные продукты. Это комплект из разных продуктов. Например коврик для йоги, ремень для йоги и скакалка. Продаётся все вместе
- Downloadable - это продукты, которые можно скачать. Пример: программное обеспечение, электронные книги, файлы.
- Gift Card (Commerce) - подарочные карты. с помощью этих продуктов можно приобрести другие продукты.


Основные различия:

- Требуется ли контроль количество? Простым нужен учёт кол-ва, а
  virtual и downloadable нет.
- Содержит ли продукт в себе другие продукты? Configurable Grouped, Bundle содержат в себе другие продукты

Вы можеть создать сonfigurable продукт, которые имеет кастомные опции, но у вас нет возможности указать процент цены для кастомный опции.

Grouped - продукт может хранить только простые и виртуальные продукты. Если открыть `vendor/magento/module-bundle/
etc/product_types.xml`, то в ноде `<allowedSelectionTypes>` видим эту конфигурацию

```
<?xml version="1.0"?>
<!--
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
-->
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Catalog:etc/product_types.xsd">
    <type name="bundle" label="Bundle Product" modelInstance="Magento\Bundle\Model\Product\Type" composite='true' indexPriority="40" sortOrder="50">
        <priceModel instance="Magento\Bundle\Model\Product\Price" />
        <indexerModel instance="Magento\Bundle\Model\ResourceModel\Indexer\Price" />
        <stockIndexerModel instance="Magento\Bundle\Model\ResourceModel\Indexer\Stock" />
        <allowedSelectionTypes>
            <type name="simple" />
            <type name="virtual" />
        </allowedSelectionTypes>
        <customAttributes>
            <attribute name="refundable" value="true"/>
        </customAttributes>
    </type>
</config>
```


Алгоритм создания нового типа продуктов

1. Создаем etc/product_types.xml. Делаем необходимую конфигурацию
2. Если продукт будет saleable, тогда необходимо добавить `etc/sales.xml`. Псмотрим на примере bundle продукта. В ноде  `<available_product_type>` указываем тип продукта

```
<?xml version="1.0"?>
<!--
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
-->
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Sales:etc/sales.xsd">
    <order>
        <available_product_type name="bundle"/>
    </order>
</config>
```
3. Для любых атрибутов, которые вы хотите применить к новому типу, необходимо обновить таблицу `catalog_eav_attribute`. По умолчанию поле apply_to пустое. Пустое значение значит, что атрибут будет отображаться для всех типов продуктов. Если колонка не пустая, то необходимо добавить новый тип в список.

4. Сконфигурировать прайс рендерер в `catalog_product_prices.xml` Пример `/magento/module-bundle/view/base/layout/
   catalog_product_prices.xml`

```
<?xml version="1.0"?>
<!--
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
-->
<layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/layout_generic.xsd">
    <referenceBlock name="render.product.prices">
        <arguments>
            <argument name="bundle" xsi:type="array">
                <item name="prices" xsi:type="array">
                    <item name="tier_price" xsi:type="array">
                        <item name="render_template" xsi:type="string">Magento_Bundle::product/price/tier_prices.phtml</item>
                    </item>
                    <item name="final_price" xsi:type="array">
                        <item name="render_class" xsi:type="string">Magento\Bundle\Pricing\Render\FinalPriceBox</item>
                        <item name="render_template" xsi:type="string">Magento_Bundle::product/price/final_price.phtml</item>
                    </item>
                    <item name="bundle_option" xsi:type="array">
                        <item name="amount_render_template" xsi:type="string">Magento_Bundle::product/price/selection/amount.phtml</item>
                    </item>
                </item>
            </argument>
        </arguments>
    </referenceBlock>
</layout>

```

5. Добавить свой тип `checkout_cart_item_renders.xml`.
   Чтобы сконфигрурировать как будет отображаться новый тип в корзине. Пример:

```
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
<!-- override checkout cart item template -->
<referenceBlock name="checkout.cart.form">
    <action method="setOverriddenTemplates">
        <argument xsi:type="array">
            <!-- list override templates -->
            <item name="default" xsi:type="string">vendor_module::cart/item/default.phtml</item>
            <item name="simple" xsi:type="string">vendor_module::cart/item/default.phtml</item>
            <item name="virtual" xsi:type="string">vendor_module::cart/item/default.phtml</item>
        </argument>
    </action>
</referenceBlock>
```

Перед тем как создаавть новый тип продуктов, необходимо понять может уже существует уже такая функциональность и не стоит создавать новый тип.


Несколько примеров.

Выбрать тип продукта для курса. Здесь может подойти virtual, если запись курса можно будет купить, downloadable

Выбрать тип продукта для очков с рецептом. Это будет простой продукт с кастомной опцией(рецепт)


Где хранятся в базе данных отношения между продуктами. Будет ли влиять на производительность колчество связей между продуктами.

Связаные продукты сохранены в таблице `catalog_product_link`. Тип
отношений хранится в `catalog_product_link_type`. Определение для отношения атрибутов сохранено в `catalog_product_link_attribute`. Значения этих атрибутов хранятся `catalog_product_link_attribute_` таблицах с префиксами `[decimal, int, varchar]`

Наличие большего количество связей между продуктами будет замедлять добавление в корзину, страницу продукта

Сравнение Related продуктов с Upsells

Upsells это маркетинговый приём, при котором выручку с покупки повышают за счёт продажи более дорогой версии продукта. Она предлагается покупателю как брендовая, новая, продвинутая.

Related - это дополнительные продукты.

Как получить программно список related продуктов. Это `Magento\Catalog\Model\ResourceModel\Product\
Link::getChildrenIds($parentId, $typeId`. Также можно через
`Magento\Catalog\Model\Product` например `getUpSellProductCollection()` c помощью метода.


Configurable параметры

Самый предпочтительный способ получения простых продуктов и configurable это метод `getChildrenIds()` в `\Magento\ConfigurableProduct\Model\
ResourceModel\Product\Type\Configurable`

Этот класс хранит информацию о атрибутах и о продукте, который ассоциируется с configurable продуктом. Из продукты мы можем получить этот класс с помощью этого метода `$product->getTypeInstance()`

Здесь самые важдные методы:

- `getUsedProductAttributes`: возвращает атрибут для конфигурабла.

- `getUsedProducts`: возвращает список продуктов ассоциирующийся с configurable продуктом

Динамически связанные продукты.

Для того чтобы создать динмамически связанные продукты лучшим решением считается написание плагина на `Magento\Catalog\Model\Product` класс. С помощью (`getCrossSellProductCollection`, `getUpSellProductCollection` и
`getRelatedProductCollection`). Перезаписав эти методы можно поменять коллекции, которые будут использоваться
