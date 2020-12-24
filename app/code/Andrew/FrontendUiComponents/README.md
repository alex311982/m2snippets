#Frontend UiComponents

###Конфигурирование UICOMPONENTS в лэйауте XML.

Например, у нас есть темплейт с описанием UI-компонента:
```
<script type="text/x-magento-init">
    {
        "*": {
                "Magento_Ui/js/core/app":
                  {
                      "components":
                      {
                          "testcomponent-knockout":
                          {
                                 "component":"[Vendor]_[Module]/js/emiproko",
                                 "template":"[Vendor]_[Module]/kotemplate",
                                  "config":
                                  {
                                          "custommessage":"Test message"
                                   }
                          }
                        }
                  }
            }
    }
</script>
```

Такое описание можно реализовать в самом лэйауте. Например, в файле app/code/[Vendor]/[Module]/view/frontend/layout/knock_index_index.xml:
```xml
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" layout="1column" xsi:noNamespaceSchemaLocation="../../../../../../../lib/internal/Magento/Framework/View/Layout/etc/page_configuration.xsd">
    <body>
        <referenceContainer name="content">
            <block class="[Vendor]/[Module]\Block\Index\Index" name="knock_index_index" template="[Vendor]_[Module]::knock_index_index.phtml">
                <arguments>
                    <argument name="jsLayout" xsi:type="array">
                        <item name="components" xsi:type="array">
                         <item name="emipro-knockout" xsi:type="array">
                            <item name="component" xsi:type="string">[Vendor]_[Module]/js/emiproko</item>
                            <item name="template" xsi:type="string">[Vendor]_[Module]/kotemplate</item>
                            <item name="config" xsi:type="array">
                                    <item name="custommessage" xsi:type="string" translate="true">Test message</item>
                          </item>
                        </item>
                         </item>
                    </argument>
                </arguments>
            </block>
        </referenceContainer>
    </body>
</page>
```

Содержимое файла-темплейта app/code/[Vendor]/[Module]/view/frontend/templates/knock_index_index.phtml станет шаблонным:
```
<div id="emipro-knockout" data-bind="scope:'emipro-knockout'">
    <!-- ko template: getTemplate() --><!-- /ko -->
</div>
<script type="text/x-magento-init">
    {
        "*":
         {
            "Magento_Ui/js/core/app":  <?php /* @escapeNotVerified */ echo $block->getJsLayout();?>
         }
    }
</script>
```

###Рендеринг группы дочерних компонентов родительского компонента

Предположим у нас есть описание наших компонентов иерархической структуры:

```xml
<?xml version="1.0"?>
 
<page layout='1column' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd" >
  <body>
      <referenceContainer name="content">
          <block class="[Vendor]/[Module]\Block\Customer\Lists" before="-" cacheable="false" template="customer/list.phtml">
              <arguments>
                  <argument name="jsLayout" xsi:type="array">
                      <item name="components" xsi:type="array">
                          <item name="customer-list" xsi:type="array">
                              <item name="component" xsi:type="string">[Vendor]_[Module]/js/view/customer/list</item>
                              <item name="config" xsi:type="array">
                                  <item name="template" xsi:type="string">[Vendor]_[Module]/customer/list</item>
                              </item>
                              <item name="children" xsi:type="array">
                                  <item name="child_a" xsi:type="array">
                                      <item name="sortOrder" xsi:type="string">2</item>
                                      <item name="component" xsi:type="string">[Vendor]_[Module]/js/view/customer/list</item>
                                      <item name="config" xsi:type="array">
                                          <item name="template" xsi:type="string">Mageplaza_HelloWorld/customer/child_a</item>
                                      </item>
                                  </item>
                                  <item name="child_b" xsi:type="array">
                                      <item name="sortOrder" xsi:type="string">1</item>
                                      <item name="component" xsi:type="string">[Vendor]_[Module]/js/view/customer/list</item>
                                      <item name="config" xsi:type="array">
                                          <item name="template" xsi:type="string">[Vendor]_[Module]/customer/child_b</item>
                                      </item>
                                      <item name="displayArea" xsi:type="string">child_b</item>
                                  </item>
                              </item>
                          </item>
                      </item>
                  </argument>
              </arguments>
          </block>
      </referenceContainer>
  </body>
</page>
```

Т.е. у нас есть родительский компонент customer-list и 2 дочерних child_a и child_b. 

Для того чтобы отрендерить дочерние компоненты в порядке знчений их sortOrder аттрибутов необходимо создать темплейт list.html:
```
<!– ko foreach: elems() –>

<!– ko template: getTemplate() –><!– /ko –>

<!– /ko –>
```

Но их можно отрендерить по названиям их регионов. Например, файл list.html станет:
```
<div data-bind="scope: requestChild('child_a')">
  <!-- ko template: getTemplate() --><!-- /ko -->
</div>

<!– ko foreach: getRegion(‘child_b’) –>
    <!– ko template: getTemplate() –><!– /ko –>
<!– /ko –>
```

###Наблюдение за свойствами компонента

Например, у нас есть компонент:
```
UiElement = requirejs('uiElement');

//define a new constructor function based on uiElement
OurClass = UiElement.extend({
    defaults:{
        tracks:{
            title: true,
            foo: true
        }
    }
});
```

Можно подписаться на изменение значений переменных title, foo:
```
//create a new object
ourViewModel = new OurClass;

//set a default value
ourViewModel.title = 'a default value';

//setup a callback
ourViewModel.on('title', function(value){
    console.log("Another callback: " + value);
});

//set the value (notice the normal javascript assignment)
//and you should see the "Another callback" output
ourViewModel.title = 'a new value';
```

Т.е. при изменении значений этих переменных в консоле появляются логи с их текущими значениями.

###Связывание UI комопнентов

####exports

Это свойство применяется для линкования локального значения с внешним. Например, у нас есть компонент:
```
{
  "defaults": {
    "exports": {
      "visible": "${ $.provider }:visibility"
    }
  }
}
```
 
Здесь - visible это ключ, ${ $.provider }:visibility - значение. Значение локального свойства visible компонента связывается со свойством visibility компонента provider.
При изменении значения visible автоматически изменяется значение visibility.

Можно указывать свойство компонента прямым перечислением его по иерархии компонентов. Например:
```
{
  "defaults": {
    "exports": {
      "items": "checkout.sidebar.summary.cart_items:items"
    }
  }
}
```

####imports

Противоположность свойству exports. Например, теперь компонент слушает свойство visibility компонента provider:
```
{
  "defaults": {
    "imports": {
      "visible": "${ $.provider }:visibility"
    }
  }
}
```

Пример реализации свойства imports через xml-конфигурации компонентов:
```
<argument name="data" xsi:type="array">
    <item name="config" xsi:type="array">
        <item name="imports" xsi:type="array">
            <item name="visible" xsi:type="string">sample_config.sample_provider:visibility</item>
        </item>
    </item>
</argument>
```

####links

Это свойство применятся для двухстороннего связывания переменных. Например:
```
{
  "defaults": {
    "links": {
      "visible": "${ $.provider }:visibility"
    }
  }
}
```

В данном примере локальное свойство visible компонента связано со свойством visibility компонента provider, и наоборот.
Чтобы работало двухстороннее связывание необходимо, чтобы обе эти переменные были наблюдаемыми через свойство track или были ko или ko-es5 наблюдаемыми.

###Связывание через ES6 литералов темплейта
Например:
```
requirejs(['uiElement'], function(Element){
    viewModelConstructor = Element.extend({
        'defaults':{
            'message':'${$.salutation} World. ',
            'salutation':'Goodbye'
        }
    });
    viewModel = new viewModelConstructor({
        'salutation':'This is still a crazy'
    });
    console.log(viewModel.message);
});
```

В данном примере имеем литерал типа ${$.salutation}. Сами ES6 литералы имеют синтаксис ${example_literal}. Т.к. литералы - это обрабатываемые строки, то в Магенто добавлен синтаксис $. для реализации ссылки. В данном случае - это ссылка на defaults объект текущего скоупа. 

###JavaScript mixins


Примесь (mixin) — это объект с набором функций, который сам по себе (отдельно от других объектов) не используется.
Используется для изменения/расширения существующего функционала модуля.
К примеру у нас есть реализованный функционал, заключенный в модуль:

```
define(
     [
         'jquery',
         'underscore',
         'ko',
         'uiComponent',
         'uiRegistry',
     ],
     function (
         $,
         _,
         ko,
         Component,
         registry,
     ) {
         'use strict';

        return Component.extend({
             // ...
             method1: function() { /* some code */ },
             method2: function() { /* some code */ }
             // ...
        });
    }
);
```

Создаем файл view/%area%/requirejs-config.js в определенной зоне (frontend, adminhtml, base), где будет происходить расширение функционала.

Например:

```
var config = {
    config: {
        mixins: {
            'VendorName_ModuleName/js/folder1/folder2/somefile' : {'My_Module/js/folder1/folder2/somefile-mixin':true}
        }
    },
}
```

Реализовываем сам миксин в файле view/%area%/web/js/folder1/folder2/somefile-mixin.js:
```
define(
     [
         'jquery',
         'underscore',
         'ko',
         'uiComponent',
         'uiRegistry',
     ],
     function (
         $,
         _,
         ko,
         Component,
         registry,
     ) {
         'use strict';

        var mixin = {
             method1: function() { /* my code */ },
        };

        return function (target) {
            return target.extend(mixin);
        };

    }
);
```

Т.е. мы подменяем код метода method1 на свой.


###Компоненты с сохранением состояния

По умолчанию после перезагрузки страницы состояние компонента берется из дэфолтных его настроек. Однако есть возможность сохранить значение определенных переменных компонента в локалсторедж браузера.
Например есть компонент:

```
OurConstructorFunction = UiElement.extend({
    'defaults':{
          'name':'aUniqueNameForTheUiElement',
          'tracks':{
              foo:true
          },              
          'statefull':{
              foo:true
          }
     }
}); 
```

Теперь если проинспектировать локалсторедж браузера - в переменной AppData сохранится текущее значение переменной foo и после перезагрузки страницы она будет иметь не дэфолтное значение, а сохраненное.

###Компоненты со свойством deps

По умолчанию компоненты модуля загружаются в произвольном порядке.
Поэтому средства связывания компонентов import, export, links могут не работать, т.к. переменная, которую мы импортируем в своем модуле из другого еще может не быть проинициализировать.

Поэтому для указания порядка загрузки компонентов внутри модуля применяют свойство deps внутри компонента. Например:
```
{
    "*": {
        "Magento_Ui/js/core/app": {
            "components": {
                "configurator": {
                    "component": "Vendor_Module/js/configurator",
                    "deps": ["configurator.price"]
                    "template": Vendor_Module/configurator",
                    "children" : {
                        "price": {
                            "component": "Vendor_Module/js/configurator/price",
                            "template": "Vendor_Module/configurator/price",
                            "displayArea": "price",
                            "price": <?php echo $block->getPrice(); ?>
                        }
                    }
                }
            }
        }
    }
}
```

Мы имеем 2 компонента configurator и дочерний price. Свойство deps компонента configurator говорит, что сначало необходимо загрузить дочерний компонент.

###Управление состоянием

Для управление состоянием связанных компонентов можно реализовать средствами самой библиотеки. Для этого сожно создать отдельный компонент, который будет импортировать состояния других компонентов, в тоже время другие компоненты могут отслеживать состояние свойств этого компонента.
Для удобства мэнэджмента глобального состояния аппликейшена есть модуль knockout-store.
Например, мы  можем установить глобальный стэйт-мэнэджмент:
```
import ko from 'knockout';
import { setState } from 'knockout-store';

const state = {
    cats: ko.observableArray(['Mr. Whiskers', 'Charles', 'Missy']),
    selectedCat: ko.observable()
};

setState(state);
```

Здесь мы устанавливаем глобальный стэйт из объекта. Дальше мы можем замапить его на наш компонент:
```
import { connect } from 'knockout-store';

function CatSelectorViewModel(params) {
    const self = this;
    self.cats = params.cats;    // from the state object, see mapStateToParams below
    self.selectCat = function(cat) {
        params.selectedCat(cat);    // also from the state object
    }
}

function mapStateToParams({ cats, selectedCat }) {  // the state object
    return { cats, selectedCat };   // properties on state to add to view model's params
}

export default connect(mapStateToParams)(CatSelectorViewModel);
```

Тем самым мы сделали двусторонне связывание глобального стэйта и нашего компонента.

В дальнейшем мы создаем еще один компонент и создаем экшен - если изменилось значение переменной, то выводим сообщение:
```
import { connect } from 'knockout-store';

function SelectedCatDisplayViewModel(params) {
    const self = this;
    // Since params.selectedCat is an observable,
    // this computed will update appropriately
    // after selectedCat is updated in the other view model.
    self.selectedCatText = ko.computed(() => `You've selected ${params.selectedCat()}!`));
}

function mapStateToParams({ selectedCat }) {
    return { selectedCat };
}

export default connect(mapStateToParams)(SelectedCatDisplayViewModel);
```