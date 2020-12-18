##Dependency Injection

Внедрение зависимостей - это специальный шаблон проектирования для программного обеспечения, 
разработанного для реализации инверсии управления и предоставления программе возможности следовать принципу инверсии зависимостей.

Внедрение зависимости состоит из четырех основных элементов:

- реализация сервисного объекта;
- клиентский объект, зависимого от сервиса;
- интерфейс, связывающий клиента с сервисом;
- инжектирующий объект, который внедряет сервис в клиента

####Способы внедрения зависимостей:

<ol>
<li>
Через конструктор.

Внедрение конструктора используется для обязательных и необязательных зависимостей объекта. 
Хорошая новость в том, что они генерируются автоматически, поэтому кодирование не требуется.
Например, в di.xml определяем какой тип будет подставляться в конструторе объекта при его создании:
```
<type name="SomeVendor\Module\Model\TestModel" shared="false">
   <arguments>
       <argument name="injectedTestObject" xsi:type="object">SomeOtherVendor\Module\Classes\TestInjectableClacc</argument>
   </arguments>
</type>
```
Пример класса, который принимает в конструкторе инжектированный объект:
```
<?php
class TestInjectableClacc
{
   protected $injectedTestObject;
 
   public function __construct(TestInjectableInterface $injectedTestObject)
   {
       $this->injectedTestObject = $injectedTestObject;
   }
}
```
При этом класс TestInjectableClacc должен имплементировать интерфейс TestInjectableInterface.
</li>
<li>
Через параметры метода.

В данном случае DI-контейнер сам резолвит класс зависимости и нет необходимости описывать внедрение зависимости в файле di.xml, как описано выше.
Например:
```
<?php
class TestInjectableClacc
{
   protected $injectedTestObject;
 
   public function someMetod(SomeVendor\Module\ClassesTestInjectableMethodArgument $injectedTestObject)
   {
       ...do something
   }
}
```
</li>
</ol>

####Особенности конфигурации обджект-мэнэджера.

Обязанности обджект-мэнэджера:

- Создание объектов внутри фабрик и прокси-объектов
- Реализация синглтонного шаблона путем возврата одного и того же общего экземпляра класса по запросу
- Управление зависимостями путем создания экземпляра предпочтительного класса, когда конструктор запрашивает его через интерфейс
- Автоматическое создание экземпляров параметров в конструкторах классов

Уровни конфигурации обджект-мэнэджера:

- (app/etc/di/*.xml) – глобальная конфигурации для всей инсталяции Магенто
- (<your module directory>/etc/di.xml) – для всего модуля
- (<your module directory>/etc/<areaname>/di.xml) – для специфической области модуля (adminhtml, frontend, webapi_rest ...)

Обджект-мэнэджер не должен быть запрашиваемым параметром класса или полученным внутри метода. 
При необходимости создать объект - необходимо вызывать определенную фабрику объектов. Фабричные классы генерируются автоматически.

####Типы зависимостей:

- инжектируемый тип объектов. 

Диспетчер объектов использует конфигурацию из файла di.xml для создания этих объектов и внедрения их в конструкторы.
Инъекционные объекты могут зависеть от других инъекционных объектов в своем конструкторе до тех пор, 
пока цепочка зависимостей не возвращается к исходному инъекционному объекту.

- не инжектируемый тип объектов.

Они получаются путем создания нового экземпляра класса каждый раз, когда они запрашиваются.

Например, вы не можете зависеть от объекта модели, такого как Product, потому что вам нужно предоставить id продукта 
или явно запросить новый пустой экземпляр для получения объекта Product через соответствующую фабрику. 
Поскольку данных по продукту еще нет при его создании через обджект-мэнэджер Magento не может внедрить этот объект.

Хотя технически этот тип зависимостей может быть внедрен в объект на усмотрение программиста.

####Типы конфигурации обджект-мэнэджера.

- через прямое указание типа внедряемого объекта. 
  
Например:
```
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<type name="Magento\Example\Type">
<arguments>
<!-- Pass simple string -->
<argument name="stringParam" xsi:type="string">someStringValue</argument>
<!-- Pass instance of Magento\Some\Type -->
<argument name="instanceParam" xsi:type="object">Magento\Some\Type</argument>
<!-- Pass true -->
<argument name="boolParam" xsi:type="boolean">1</argument>
<!-- Pass 1 -->
<argument name="intParam" xsi:type="number">1</argument>
<!-- Pass application init argument, named by constant value -->
<argument name="globalInitParam" xsi:type="init_parameter">Magento\Some\Class::SOME_CONSTANT</argument>
<!-- Pass constant value -->
<argument name="constantParam" xsi:type="const">Magento\Some\Class::SOME_CONSTANT</argument>
<!-- Pass null value -->
<argument name="optionalParam" xsi:type="null"/>
<!-- Pass array -->
<argument name="arrayParam" xsi:type="array">
<!-- First element is value of constant -->
<item name="firstElem" xsi:type="const">Magento\Some\Class::SOME_CONSTANT</item>
<!-- Second element is null -->
<item name="secondElem" xsi:type="null"/>
<!-- Third element is a subarray -->
<item name="thirdElem" xsi:type="array">
<!-- Subarray contains scalar value -->
<item name="scalarValue" xsi:type="string">ScalarValue</item>
<!-- and application init argument -->
<item name="globalArgument " xsi:type="init_parameter">Magento\Some\Class::SOME_CONSTANT</item>
</item>
</argument>
</arguments>
</type>
</config>
```

Как видим - мы можем внедрять различные типы зависимостей - объекты, строки, числа, null, init_parameter, константы, массивы.

- через предпочтения. 
  
Например, в следующем примере мы указываем, что класс Magento_Backend_Model_Url, имплементирующий интерфейс Magento_Core_Model_UrlInterface, 
будет везде применяться для создания объектов везде, где требуется внедрение зависимости от этого интрефейса:
```
<config>
<preference for="Magento_Core_Model_UrlInterface" type="Magento_Backend_Model_Url" />
</config>
```

Используя вышеописаную цепочку резолвов зависимостей в файлах di.xml, можно переопределять предпочтения в своих модулях.

По умолчанию все объекты в контейнере зависимостей - shared. Т.е. когда обджект-мэнэджеру необходим объект, например, для внедрения как зависимость в какой-то класс, 
то он не инстанциирует новый объект, а берет его из заготовки и инжектит. Тем самым вызывая свой метод create, а не get.
Но можно указать в di.xml, что всегда необходимо создавать новый экземпляр объекта, указав для него shared="false". Например:
```
<type name="FooBazModelBar" shared="false"/>
```

Для создания объектов внутри методов классов используют фабрики. Классы-фабрики генерируются автоматически при необходимости их использования.
Пример автоматически сгенерированной фабрики:
```
class Magento\Core\Model\Config\BaseFactory
{
    protected $_objectManager;
 
    public function __construct(Magento\Framework\ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }
 
    public function create($sourceData = null)
    {
        return $this->_objectManager->create('Magento\Core\Model\Config\Base', array('sourceData' => $sourceData));
    }
}
```
Как мы видим для создания объектов внутри используется обджект-мэнэджер.

Всякий раз при изменении какого-либо di.xml необходимо перекомпилировать di-контейнер, вызвав в консоде команду:
```
php bin/magento setup:di:compile
```