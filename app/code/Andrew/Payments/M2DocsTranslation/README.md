#Magento payment provider gateway


`Magento payment provider gateway` - это механизм, который позволяет интегрировать платёжные системы в M2.
Как результат ты можешь создать обработчик транзакций основанный на деталях заказа.

На следующей картинке показано схематическое взаимодействие M2 с платёжными системами.

![Alt text](./payment-schema.png?raw=true "payment-schema")

M2 платёжный провайдер поддерживает следующие операции:

- `authorize`: процесс авторизации транзакции. Необходимая сумма блокируется на счету клиента, но не выводится.
- `sale`: процесс авторизации транзакции и списание средств со счёта клиента происходит автоматически.
- `capture`: списание раннее разрешённую сумму со счёта клиента.
- `refund`: возврат раннее списанных клиентских средств 
- `void`: отмена перевода клиентских средств

На следующей диаграмме показаны базовые компоненты `Magento payment provider gateway`:

![Alt text](./commands-components.png?raw=true "commands-components")

Команда - это компонент, который принимает обязательные данные для платёжной системы и отправляет запросы, получает и обрабатывает ответы
с платёжных систем. Для каждой операции добавлена своя команда.

Каждая команда состоит из таких компонентов:

- `Request Builder` формирует массив данных для платёжной системы, основанный на заказе.
- `Transfer Factory` создаёт объект из данных, сформированных с помощью `Request Builder`, который будет использовать `Gateway Client` для процессинга запросов.
- `Gateway Client`  получает специфичные аргументы и отправляет низкоуровневые запросы на платёжные системы.
- `Response Handler` - этот компонент изменяет статус заказа и payment в М2.
- `Response Validator` - проверяет ответ платёжной системы и формирует сообщения для клиентов и продавцов.

А взаимодействие между компонентами происходит так:

![Alt text](./interaction-components.png?raw=true "interaction-components")

Базовый интерфейс для реализации команды `\Magento\Payment\Gateway\CommandInterface`. Он реализует поведенческий паттерн проектирования `Command`.

Добавление команды.

Команды добавляются с помощью `virtualTypes`.

Пример добавления для `app/code/Magento/Braintree/etc/di.xml`

```
<virtualType name="BraintreeAuthorizeCommand" type="Magento\Payment\Gateway\Command\GatewayCommand">
    <arguments>
        <argument name="requestBuilder" xsi:type="object">BraintreeAuthorizeRequest</argument>
        <argument name="transferFactory" xsi:type="object">Magento\Braintree\Gateway\Http\TransferFactory</argument>
        <argument name="client" xsi:type="object">Magento\Braintree\Gateway\Http\Client\TransactionSale</argument>
        <argument name="handler" xsi:type="object">BraintreeAuthorizationHandler</argument>
        <argument name="validator" xsi:type="object">Magento\Braintree\Gateway\Validator\ResponseValidator</argument>
        <argument name="errorMessageMapper" xsi:type="object">Magento\Braintree\Gateway\ErrorMapper\VirtualErrorMessageMapper</argument>
    </arguments>
</virtualType>
```

Все команды должны быть добавлены в общий пул. Это конфигурируется с помощью `virtualTypes`

Пул команд реализует интерфейс `\Magento\Payment\Gateway\Command\CommandPoolInterface`, который реализует паттерн проектирования `Pool`.

Пример добавления Пула команд:

```
...
<!-- BraintreeCommandPool - a command pool for the Braintree payments provider -->
<virtualType name="BraintreeCommandPool" type="Magento\Payment\Gateway\Command\CommandPool">
    <arguments>
        <argument name="commands" xsi:type="array">
            <item name="authorize" xsi:type="string">BraintreeAuthorizeCommand</item>
            <item name="sale" xsi:type="string">BraintreeSaleCommand</item>
            <item name="capture" xsi:type="string">BraintreeCaptureStrategyCommand</item>
            ...
        </argument>
    </arguments>
</virtualType>
...
<!-- Adding BraintreeCommandPool to the Braintree payment method configuration:-->
<virtualType name="BraintreeFacade" type="Magento\Payment\Model\Method\Adapter">
    <arguments>
        ...
        <argument name="commandPool" xsi:type="object">BraintreeCommandPool</argument>
    </arguments>
</virtualType>
...
```


`Magento\Payment\Gateway\Command\CommandPool` реализует  `CommandPoolInterface` и принимает список команд как необязательные аргументы для конструктора.

#Request Builder
`Request Builder` - это компонент команды, который ответственный за создание запроса из нескольких частей. Это позволяет реализовывать комплекс стратегий. 
Каждый билдер имеет простую логику или содержит `builder composites`.

Базовый интерфейс для билдера `\Magento\Payment\Gateway\Request\BuilderInterface`.

`Builder composites`

`\Magento\Payment\Gateway\Request\BuilderComposite` - это контейнер содержащий список реализаций `\Magento\Payment\Gateway\Request\BuilderInterface`.
Он получает список классов или  `types`, или `virtual type` имён, которые создаются в `BuilderComposite::build([])`. В общем у вас столько объектов сколько необходимо для создания запроса.

`BuilderComposite` реализовывает паттерн проектирования `Composite`.

Система объединения определяется в `BuilderComposite::merge()`. Если вам необходимо изменить стратегию, вам необходимо добавить свою реализацию `BuilderComposite`.

`Builder composites` добавляются с помощью DI в `di.xml`. `BuilderComposite` должен включать в себя простые билдеры и билдер композиты.

Пример добавления билдер композитов `app/code/Magento/Braintree/etc/di.xml`:

```
...
<!--  is a builder composite comprising a number of builders -->
<virtualType name="BraintreeAuthorizeRequest" type="Magento\Payment\Gateway\Request\BuilderComposite">
    <arguments>
        <argument name="builders" xsi:type="array">
            <item name="customer" xsi:type="string">Magento\Braintree\Gateway\Request\CustomerDataBuilder</item>
            <item name="payment" xsi:type="string">Magento\Braintree\Gateway\Request\PaymentDataBuilder</item>
            <item name="channel" xsi:type="string">Magento\Braintree\Gateway\Request\ChannelDataBuilder</item>
            <item name="address" xsi:type="string">Magento\Braintree\Gateway\Request\AddressDataBuilder</item>
            <item name="vault" xsi:type="string">Magento\Braintree\Gateway\Request\VaultDataBuilder</item>
            <item name="3dsecure" xsi:type="string">Magento\Braintree\Gateway\Request\ThreeDSecureDataBuilder</item>
            <item name="device_data" xsi:type="string">Magento\Braintree\Gateway\Request\KountPaymentDataBuilder</item>
            <item name="dynamic_descriptor" xsi:type="string">Magento\Braintree\Gateway\Request\DescriptorDataBuilder</item>
            <item name="store" xsi:type="string">Magento\Braintree\Gateway\Request\StoreConfigBuilder</item>
            <item name="merchant_account" xsi:type="string">Magento\Braintree\Gateway\Request\MerchantAccountDataBuilder</item>
        </argument>
    </arguments>
</virtualType>
...
<!-- The same BraintreeAuthorizeRequest builder composite is a part of the BraintreeSaleRequest builder composite -->
<virtualType name="BraintreeSaleRequest" type="Magento\Payment\Gateway\Request\BuilderComposite">
    <arguments>
        <argument name="builders" xsi:type="array">
            <item name="authorize" xsi:type="string">BraintreeAuthorizeRequest</item>
            <item name="settlement" xsi:type="string">Magento\Braintree\Gateway\Request\SettlementDataBuilder</item>
        </argument>
    </arguments>
</virtualType>
```
#Transfer Factory
`Transfer Factory` - создаёт объект из массива данных полученных от билдеров.

Базовый интерфейс для `Transfer Factory` `Magento\Payment\Gateway\Http\TransferFactoryInterface`.

#Gateway Client

`Gateway Client` - это компонент, который передаёт `payload`(данные из ордера, клиентские данные, доступы продавцов к api платёжной системы) в платёжную систему и получает ответ

Базовая реализация для `Gateway Client` `Magento\Payment\Gateway\Http\ClientInterface`.

`Gateway Client` - получает `Transfer` объект, который был сформирован с помощью `Transfer Factory`. `Gateway Client` должен быть сконфигурирован с помощью di.

Из коробки M2 предлагает две реализации `Gateway Client`:

 - `\Magento\Payment\Gateway\Http\Client\Zend`
 - `\Magento\Payment\Gateway\Http\Client\Soap`

Пример добавления как можно добавить `Zend Client`:

```
...
<virtualType name="HtmlConverterZendClient" type="Magento\Payment\Gateway\Http\Client\Zend">
    <arguments>
        <argument name="converter" xsi:type="object">Magento\Payment\Gateway\Http\Converter\HtmlFormConverter</argument>
        <argument name="logger" xsi:type="object">CustomLogger</argument>
    </arguments>
</virtualType>
...
```

#Response Validator

`Response Validator` - это компонент M2 механизма, который выполняет проверку ответа. 
Этот компонент может содержать как низко-уровневое форматирование данных, так и проверку данных и даже выполнение какой-то бизнес логики.

`Response Validator` может возвращать результирующий объект в виде булевого значения и список ошибок `\Magento\Framework\Phrase`

`Response Validator` реализовывает интерфейс `Magento\Payment\Gateway\Validator\ValidatorInterface`
Результат должен реализовывать интерфейс `Magento\Payment\Gateway\Validator\ResultInterface`

`Payment` может иметь, как один валидатор так и несколько. Если несколько то нужно чтобы они были добавлены в `validator’s pool` с помощью `DI` 

Полезные реализации:

- `\Magento\Payment\Gateway\Validator\AbstractValidator` - абстрактный класс, который возвращает `ResultObject`.
- `\Magento\Payment\Gateway\Validator\ValidatorComposite` - цепочка Validator объектов, которые будут выполняться по очереди. Результат будет сформирован в `ResultObject`. 
  перебор валидаторов будет остановлен если валидатор не сможет выполниться.
- `\Magento\Payment\Gateway\Validator\Result` базовый класс для результирующего объекта. 

#Response Handler

`Response Handler` - это компонент M2 механизма, который обрабатывает ответы от платёжных систем. 

Выполняет такие действия:

- Изменяет статуc заказа
- Сохраняет данные из ответа
- Отправляет сообщение на почту

Базовый интерфейс который можно использовать `Magento\Payment\Gateway\Response\HandlerInterface`

#Error Code Mapping
Ответ от платёжных систем может содержать коды ошибок, которые необходимо превратить во что-то читабельное.

В М2 из коробки есть интерфейс и реализация для мапинга ошибок.
`\Magento\Payment\Gateway\ErrorMapper\ErrorMessageMapperInterface` 
`\Magento\Payment\Gateway\ErrorMapper\ErrorMessageMapper`

Добавление маппинга. Создаём `<gateway_name>_error_mapping.xml`, но вы можете использовать любое имя, которое вам нравиться.
Этот файл можно поместить `etc/` или `etc/frontend`, или `etc/adminhtml`, таким образом можно добавить разные маппинги ошибок для клиентов и для продавцов.

Пример:

```
<mapping xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Payment:etc/error_mapping.xsd">
    <message_list>
        <message code="81703" translate="true">Credit card type is not accepted by this merchant account.</message>
        <message code="81706" translate="true">CVV is required.</message>
        <message code="81707" translate="true">CVV must be 4 digits for American Express and 3 digits for other card types.</message>
        ...
    </message_list>
</mapping>
```

