# Magento 2 простой шлюзовый метод

В Магенто реализована функциональность - возможность добавления кастомного платежного метода.
После этого добавленный метод можно будет конфигурировать в админке `Admin panel > Stores > Settings > Configuration > Sales > Payment Methods`.


## Создание модуля

1. Создаем файл registration.php

``` php
<?php
\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    '[Vendor]_[Module]',
    __DIR__
);
```

2. Указываем зависисмости нашего модуля от других в файле `module.xml`

``` xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Module/etc/module.xsd">
    <module name="[Vendor]_[Module]" setup_version="0.1.0">
        <sequence>
            <module name="Magento_Sales"/>
            <module name="Magento_Payment"/>
            <module name="Magento_Checkout"/>
            <module name="Magento_Directory" />
            <module name="Magento_Config" />
        </sequence>
    </module>
</config>
```

3. Create `config.xml` file in `etc` folder.

``` xml
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Store:etc/config.xsd">
    <default>
        <payment>
            <sample_gateway>
                <debug>1</debug>
                <active>0</active>
                <model>SamplePaymentGatewayFacade</model>
                <merchant_gateway_key backend_model="Magento\Config\Model\Config\Backend\Encrypted" />
                <order_status>pending_payment</order_status>
                <payment_action>authorize</payment_action>
                <title>Payment method (SampleGateway)</title>
                <currency>USD</currency>
                <can_authorize>1</can_authorize>
                <can_capture>1</can_capture>
                <can_void>1</can_void>
                <can_use_checkout>1</can_use_checkout>
                <is_gateway>1</is_gateway>
                <sort_order>1</sort_order>
                <debugReplaceKeys>MERCHANT_KEY</debugReplaceKeys>
                <paymentInfoKeys>FRAUD_MSG_LIST</paymentInfoKeys>
                <privateInfoKeys>FRAUD_MSG_LIST</privateInfoKeys>
            </sample_gateway>
        </payment>
    </default>
</config>
```

3. Создаем виртуальные типы в di.xml, которые будут реализовывать логику нашего метода, путем конфигурирования кастомными типами, содержащие логику формирования риквеста, обработки респонса от платежной системы.
4. Внедрить изменения в интерфейсе, необходимые для отображения способа оплаты для клиента

``` xml
<?xml version="1.0" ?>
<pagepage layout="1column" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
          xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    <body>
        <referenceBlock name="checkout.root">
            <arguments>
                <argument name="jsLayout" xsi:type="array">
                    <item name="components" xsi:type="array">
                        <item name="checkout" xsi:type="array">
                            <item name="children" xsi:type="array">
                                <item name="steps" xsi:type="array">
                                    <item name="children" xsi:type="array">
                                        <item name="billing-step" xsi:type="array">
                                            <item name="children" xsi:type="array">
                                                <item name="payment" xsi:type="array">
                                                    <item name="children" xsi:type="array">
                                                        <item name="renders" xsi:type="array">
                                                            <item name="children" xsi:type="array">
                                                                <item name="offline-payments" xsi:type="array">
                                                                    <item name="component" xsi:type="string">Magento_OfflinePayments/js/view/payment/offline-payments</item>
                                                                    <item name="methods" xsi:type="array">
                                                                        <item name="simple" xsi:type="array">
                                                                            <item name="isBillingAddressRequired" xsi:type="boolean">true</item>
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
                        </item>
                    </item>
                </argument>
            </arguments>
        </referenceBlock>
    </body>
</pagepage>
```

/view/frontend/web/js/view/payment/simple.js


``` js
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (Component,
              rendererList) {
        'use strict';
        rendererList.push(
            {
                type: 'simple',
                component: '[Vendor]_[Module]/js/view/payment/method-renderer/simple-method'
            }
        );
        return Component.extend({});
    }
);
```

/view/frontend/web/js/view/payment/method-renderer/simple-method.js

``` js
define(
    [
        'Magento_Checkout/js/view/payment/default'
    ],
    function (Component) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Mageplaza_Payment/payment/simple'
            },
            getMailingAddress: function () {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
        });
    }
);
```

/view/frontend/web/template/payment/simple.html

``` php
<div class="payment-method" data-bind="css: {'_active': (getCode() == isChecked())}">
    <div class="payment-method-title field choice">
        <input type="radio"
               name="payment[method]"
               class="radio"
               data-bind="attr: {'id': getCode()}, value: getCode(), checked: isChecked, click: selectPaymentMethod, visible: isRadioButtonVisible()"/>
        <label data-bind="attr: {'for': getCode()}" class="label"><span data-bind="text: getTitle()"></span></label>
    </div>
    <div class="payment-method-content">
        <!-- ko foreach: getRegion('messages') -->
        <!-- ko template: getTemplate() --><!-- /ko -->
        <!--/ko-->
        <div class="payment-method-billing-address">
            <!-- ko foreach: $parent.getRegion(getBillingAddressFormName()) -->
            <!-- ko template: getTemplate() --><!-- /ko -->
            <!--/ko-->
        </div>
        <div class="checkout-agreements-block">
            <!-- ko foreach: $parent.getRegion('before-place-order') -->
            <!-- ko template: getTemplate() --><!-- /ko -->
            <!--/ko-->
        </div>
        <div class="actions-toolbar">
            <div class="primary">
                <button class="action primary checkout"
                        type="submit"
                        data-bind="
                        click: placeOrder,
                        attr: {title: $t('Place Order')},
                        css: {disabled: !isPlaceOrderActionAllowed()},
                        enable: (getCode() == isChecked())
                        "
                        disabled>
                    <span data-bind="i18n: 'Place Order'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
```
