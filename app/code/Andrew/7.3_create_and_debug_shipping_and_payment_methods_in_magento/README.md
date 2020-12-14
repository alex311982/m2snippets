# Magento 2 создание и дэбаг методов доставки и оплаты

###Шлюзовые, офлайновые, хостинговые методы оплаты.

Способы оплаты в значительной степени попадают в одну из этих категорий. Шлюз - это
когда платежные реквизиты (например, кредитная карта) отправляются в Magento, а затем - к мерчанту.
Это также может относиться к токенизированной кредитной карте (Braintree или Stripe) на фронте и авторизация/захват происходит
на стороне Magento.

Автономный режим предназначен для типов платежей, которые не связаны с внешним сервисом. Например, Check/Money Order, Bank Transfer, Purchase Order и
Cash on Delivery (находится в админке \Magento\OfflinePayments). Эти типы оплаты предлагают Credit Memo, но только для всего заказа, а не для каждого инвойса.

Хостинговые методы оплаты включают перенаправление клиента к третьей стороне.Раньше это был старый PayPal Express в Magento 1. 
Теперь есть только два подобного типа оплаты, которые размещаются в Magento, - это Payflow Express и Payflow Bill.

Когда кастомер выбирает хостинговый метод оплаты на чекауте, он кликает на кнопку оплаты, и его перенаправляет на сайт метода оплаты. После ввода кредитных данных, его перенеправляют снова на сайт магазина.

###Шлюзовые методы оплаты.

Подобные типы оплаты в большинстве своем используют икстеншен \Magento\Payment\Model\Method\Cc. Этот икстеншен запрещен начиная с версии Magento 2.1.

Для таких типов оплаты используется класс-адаптер \Magento\Payment\Model\Method\Adapter. Он служит для конфигурации метода оплаты.
Класс-адаптер служит для конфигурации метода оплаты. Метод  \Magento\Payment\Model\Method\Adapter::getConfigData.

Каждый шлюзовый метод оплаты должен содержать:

- Пул обработчиков. В основном это загрузчик конфигурации. Однако, можно имплементировать кастомный обработчик для каждого значения. Например,
  can_void обработчик.
  
- Пул классов валидаторов. Даже если валидаторы не нужны, они все-ракно должны быть указаны.

- Пул команд. 

###Пул команд

Команды необходимы для реализации экшенов. Список команд класса \Magento\Payment\Model\Method\Adapter:

- fetch_transaction_information
- order
- authorize
- capture
- refund
- cancel
- void
- acceptPayment
- denyPayment

Для добавления новой команды необходимо:

1. Найти пул команд (или виртуального типа) для метода оплаты.
2. Добавить команду в аргумент commands.
3. Создать класс-конфигурации Magento\Payment\Gateway\Command\GatewayCommand.
4. Команда должна реализовывать методы requestBuilder, transferFactory, client, handler.

Проверка доступности метода для оплаты:

1. Вызывается метод isActive адаптера платежного метода
2. Идет внутренний вызов getConfiguredValue
3. Идет проверка пула обработчиков для данного значения
4. Если хэндлер не обнаружен и метод оплаты верно сконфигурирован, то вызывается метод \Magento\Payment\Gateway\Config\Config::getValue для загрузки значения из конфигурации стора.

###Расчет стоимости доставки

Для расчета вызывается метод \Magento\Quote\Model\Quote\Address\Total\Shipping::collect, который унаследуется от \Magento\Quote\Model\Quote\Address\Total\AbstractTotal.
Метод \Magento\Quote\Model\Quote\Address::requestShippingRates собирает данные для риквеста рейта доставки. Этот объект должен содержать всю информацию, необходимую для подсчета стоимости доставки.
FreeMethodWeight - сколько веса не должно учавствовать в калькуляции цены доставки. Переменная limit_carrier в квоте-адреса позволяет сузить опции доставки до одной.

Важные методы:

- \Magento\Shipping\Model\Shipping::collectRates - собирает всех провайдеров-доставки. Если ваш провайдер не учитывается, то необходимо обратиться к этому методу.
- \Magento\Shipping\Model\Shipping::collectCarrierRates - если провайдер активный, то загружает данные по его рейтам доставки.

После подсчета калькулятор возвращает объект класса \Magento\Shipping\Model\Rate\Result. Это контейнер объектов-рейтов класса \Magento\Quote\Model\Quote\Address\RateResult\Method.
Например, есть провайдер доставки FedEx.
Есть нюанс работы сервисов доставки - если один из них будетне работать должным образом - он может вывести из строя весь магазин.
По умолчанию провайдеры доставки предлагают бесплатную доставку. 
Цена доставки расчитывается методом \Magento\Shipping\Model\Carrier\AbstractCarrierOnline::getMethodPrice.
Если бесплатная доставка не возможная, то вызывается метод для расчета цены доставки \Magento\Shipping\Model\Carrier\AbstractCarrier::getFinalPriceWithHandlingFee.


####Дэбаг рейтов доставки и кастомизация таблицы рейтов доставки.

Во многих случаях рейты доставки могут быть задизейблены (нет возможности доставки в выбраную страну, ошибка, вернувшаяся от провайдеров). Таблица рейтов предоставляет одно имя провайдера доставки к списку рейтов. Рейты применяются к вэбсайтам и не могут быть применены к глобальному скоупу. Доступные параметры таблицы рейтов:
- страна
- регион/штат
- zip/почтовый код
- сабтотал ордера
- цена доставки

Расчет цены доставки по таблице рейтов производится методом \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate::getRate.

Шлюзовый метод оплаты или AbstractMethod?
Абстрактный метод для оплат был запрещен. Однако все офлайн методы оплаты используют его.

Что такое офлайн методы оплаты. У таких платежных систем нет коннекта со сторонними шлюзами. Например, оплата по чеку, купленный ордер или ручной перевод средств.

Методы оплаты должны обеспечивать частичный инвойс. Есть 2 переменные в классах методов доставки - $_canCapturePartial и $_canRefundInvoicePartial.

Для создания хостингового метода оплаты необходимо реализовать адаптер с методом getConfigData, который и будет возвращать базовую урлу для редиректа на сйт системы оплаты.
Например, метод оплаты Payflow Express.

Добавление нового метода доставки:

- создать дэфолтные настройки метода в файле etc/config.xml. Например,
```
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Store:etc/config.xsd">
    <default>
        <carriers>
            <newpost>
                <active>1</active>
                <autocomplete_address>0</autocomplete_address>
                <title>New Post</title>
                <name>New Post Shipping</name>
                <sallowspecific>0</sallowspecific>
                <shipping_cost>0</shipping_cost>
                <cargo_list>Parcel,Cargo,Documents,TiresWheels,Pallet</cargo_list>
                <allowed_methods>DoorsDoors,DoorsWarehouse,WarehouseWarehouse,WarehouseDoors</allowed_methods>
                <model>AKN\NewPost\Model\Carrier\NewPost</model>
            </newpost>
        </carriers>
    </default>
</config>
```
- добавить конфигурационые опции в etc/adminhtml/system.xml

- создать класс, имплементирующий интерфейс \Magento\Shipping\Model\Carrier\CarrierInterface. Например, \AKN\NewPost\Model\Carrier\NewPost.

Кастомизация таблицы рейтов:

- таблица рейтов доставки хранится в таблице shipping_tablerate.
- для добавления дополнительного параметра в запрос к таблице необходимо создать плагин для метода \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\RateQuery::prepareSelect
- запрос доступен вызовом метода \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\RateQuery::getRequest

Класс \Magento\Shipping\Model\Carrier\AbstractCarrierOnline позволяет кэшировать квоты. Его метод  _getCachedQuotes($requestString) генерирует кэш-ключ для сохранения риквеста и для получения его в альнейшем из кэша по этому ключу.