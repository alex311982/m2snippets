##Totals в magento 2

В Magento 2 можно легко добавлять и изменять существующие тоталы.
Кастомные тоталы могут быть использованы для добавления налогов и скидок в чекауте и в корзине.

Рассмотрим на основе простого модуля как это сделать.

Для начала создадим <module_dir>/etc/sales.xml в своем модуле.
Этот файл используется для декларирования всех возможных Magento тоталов
````
// app/code/[Vendor]/[Module]/etc/sales.xml

<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Sales:etc/sales.xsd">
    <section name="quote">
        <group name="totals">
            <item name="custom_amount" instance="[Vendor]\[Module]\Model\Total\Custom" sort_order="150"/>
        </group>
    </section>
</config>
````
Здесь мы добавляем  `\Vendor\Module\Model\Totals\Custom` класс как свою собственную тотал модель.

Этот класс должен наследовать  `\Magento\Quote\Model\Quote\Address\Total\AbstractTotal` и реализовывать `collect` и `fetch`  методы.
`collect` метод применяется для подсчета наших собственного тотала.

`fetch` возвращает `total_code`, `title` и `value`. 
Параметр `\Magento\Quote\Model\Quote\Address\Total $total` в методе `collect` позволяет нам влиять на результат других тоталов. 

Все зависит от задачи. Также есть вариант использовать плагина для изменения значений тоталов.

````
// app/code/[Vendor]/[Module]/Model/Total/Custom.php

<?php
namespace [Vendor]\[Module]\Model\Total;

use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address\Total;

class Custom extends AbstractTotal
{
    /**
     * Custom constructor.
     */
    public function __construct()
    {
        $this->setCode('custom_amount');
    }

    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return $this;
        }
        $amount = 10; // A surcharge of '10' as an example.

        $total->setTotalAmount('custom_amount', $amount);
        $total->setBaseTotalAmount('custom_amount', $amount);
        $total->setCustomAmount($amount);
        $total->setBaseCustomAmount($amount);
        $total->setGrandTotal($total->getGrandTotal() + $amount);
        $total->setBaseGrandTotal($total->getBaseGrandTotal() + $amount);

        return $this;
    }

    /**
     * @param Total $total
     */
    protected function clearValues(Total $total)
    {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('discount_tax_compensation', 0);
        $total->setBaseTotalAmount('discount_tax_compensation', 0);
        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);
    }

    /**
     * @param Quote $quote
     * @param Total $total
     * @return array
     */
    public function fetch(Quote $quote, Total $total)
    {
        return [
            'code' => $this->getCode(),
            'title' => 'Custom Amount',
            'value' => 10
        ];
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Custom Amount');
    }
}
````
 ###Отображение кастомных тоталов
В основном кастомный тотал необходимо вывести на странице чекаута и корзины.
Так как в magento 2 корзина и чекаут разработаны на базе `Knokout JS`, мы должны добавить новый js component  в `checkout_cart_index.xml` и `checkout_index_index.xml`
Добавляем компоненты

###checkout_cart_index.xml
````
// app/code/[Vendor]/[Module]/view/frontend/layout/checkout_cart_index.xml

<?xml version="1.0"?>
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    <body>
        <referenceBlock name="checkout.cart.totals">
            <arguments>
                <argument name="jsLayout" xsi:type="array">
                    <item name="components" xsi:type="array">
                        <item name="block-totals" xsi:type="array">
                            <item name="children" xsi:type="array">
                                <item name="custom_amount" xsi:type="array">
                                    <item name="component" xsi:type="string">[Vendor]_[Module]/js/view/checkout/cart/totals/custom_amount</item>
                                    <item name="sortOrder" xsi:type="string">20</item>
                                    <item name="config" xsi:type="array">
                                        <item name="template" xsi:type="string">[Vendor]_[Module]/checkout/cart/totals/custom_amount</item>
                                        <item name="title" xsi:type="string">Custom Amount</item>
                                    </item>
                                </item>
                            </item>
                        </item>
                    </item>
                </argument>
            </arguments>
        </referenceBlock>
    </body>
</page>

````

###checkout_index_index.xml

````
// app/code/[Vendor]/[Module]/view/frontend/layout/checkout_index_index.xml

<?xml version="1.0"?>
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" layout="1column" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    <body>
        <referenceBlock name="checkout.root">
            <arguments>
                <argument name="jsLayout" xsi:type="array">
                    <item name="components" xsi:type="array">
                        <item name="checkout" xsi:type="array">
                            <item name="children" xsi:type="array">
                                <item name="sidebar" xsi:type="array">
                                    <item name="children" xsi:type="array">
                                        <item name="summary" xsi:type="array">
                                            <item name="children" xsi:type="array">
                                                <item name="totals" xsi:type="array">
                                                    <item name="children" xsi:type="array">
                                                        <item name="custom_amount" xsi:type="array">
                                                            <item name="component"  xsi:type="string">[Vendor]_[Module]/js/view/checkout/cart/totals/custom_amount</item>
                                                            <item name="sortOrder" xsi:type="string">20</item>
                                                            <item name="config" xsi:type="array">
                                                                <item name="template" xsi:type="string">[Vendor]_[Module]/checkout/cart/totals/custom_amount</item>
                                                                <item name="title" xsi:type="string">Custom Amount</item>
                                                            </item>
                                                        </item>
                                                    </item>
                                                </item>
                                                <item name="cart_items" xsi:type="array">
                                                    <item name="children" xsi:type="array">
                                                        <item name="details" xsi:type="array">
                                                            <item name="children" xsi:type="array">
                                                                <item name="subtotal" xsi:type="array">
                                                                    <item name="component" xsi:type="string">Magento_Tax/js/view/checkout/summary/item/details/subtotal</item>
                                                                </item>
                                                            </item>
                                                        </item>
                                                    </item>
                                                </item>
                                            </item>
                                        </item>
                                    </item>
                                </item>
                            </item>
                        </item>
                    </item>
                </argument>
            </arguments>
        </referenceBlock>
    </body>
</page>
````

Дальше создаём `custom_amount.js`

````
// app/code/[Vendor]/[Module]/view/frontend/web/js/view/checkout/cart/totals/custom_amount.js

define(
    [
        '[Vendor_Module]/js/view/checkout/summary/custom_amount'
    ],
    function (Component) {
        'use strict';

        return Component.extend({

            /**
             * @override
             */
            isDisplayed: function () {
                return this.getPureValue() !== 0;
            }
        });
    }
);

````

и `custom_amount.html`

````
// app/code/[Vendor]/[Module]/view/frontend/web/template/checkout/cart/totals/custom_amount.html

<!-- ko if: isDisplayed() -->
<tr class="totals custom_amount excl">

    <th class="mark" colspan="1" scope="row" data-bind="text: title"></th>
    <td class="amount">
        <span class="price" data-bind="text: getValue()"></span>
    </td>
</tr>
<!-- /ko -->
````

В следующем js файле getValue и  getPureValue возвращают значения наших кастомных тоталов.  Метод getValue метод добавляет две цифры в конце и значок валюты.

````
// app/code/[Vendor]/[Module]/view/frontend/web/js/view/checkout/summary/custom_amount.js
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, quote, priceUtils, totals) {
        "use strict";
        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: '[Vendor]_[Module]/checkout/summary/custom_amount'
            },
            totals: quote.getTotals(),
            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,

            isDisplayed: function() {
                return this.isFullMode() && this.getPureValue() !== 0;
            },

            getValue: function() {
                var price = 0;
                if (this.totals()) {
                    price = totals.getSegment('custom_amount').value;
                }
                return this.getFormattedPrice(price);
            },
            getPureValue: function() {
                var price = 0;
                if (this.totals()) {
                    price = totals.getSegment('custom_amount').value;
                }
                return price;
            }
        });
    }
);
````
Теперь наш кастомный тотал будет отображаться в корзине и в чекауте

###Order emails
Для добавления нового тотала в имэйл заказа, необходимо добавить новый блок в `sales_email_order_items.xml`

````
<?xml version="1.0" encoding="UTF-8"?>
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    <body>
        <referenceBlock name="order_totals">
            <block class="[Vendor]\[Module]\Block\Order\CustomTotal" name="order.totals.custom"/>
        </referenceBlock>
    </body>
</page>

````

И создать этот блок

````
<?php

namespace [Vendor]\[Module]\Block\Order;

class CustomTotal extends \Magento\Framework\View\Element\AbstractBlock
{
    public function initTotals()
    {
        $orderTotalsBlock = $this->getParentBlock();
        $order = $orderTotalsBlock->getOrder();
        if ($order->getCustomAmount() > 0) {
            $orderTotalsBlock->addTotal(new \Magento\Framework\DataObject([
                'code' => 'custom_total',
                'label' => __('Custom Total'),
                'value' => $order->getCustomAmount(),
                'base_value' => $order->getCustomBaseAmount(),
            ]), 'subtotal');
        }
    }
}
````

После этого кастомный тотал будет отображаться в имэйле.
