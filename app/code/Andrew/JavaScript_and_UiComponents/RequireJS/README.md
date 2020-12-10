# Magento 2 RequireJS and Magento Fundamentals

К примеру, необходимо применить виджет (jQuery UI Widget) для элемента страницы сайта. Способы интеграции RequireJS модулей на странице сайта:

1. В файле темплейта непосредственной инициализацией необходимых модулей и вызовом требуемого метода:
```
<script>
    require([
        'jquery',
        'accordion'], function ($) {
        $("#element").accordion();
    });
</script>
```

Т.е. в файле темплейта мы инициализируем AMD-модули jquery, accordion и вызвываем метод accordion на элементе страницы с id=element.

2. В файле темплейта путем добавления Магентовского аттрибута data-mage-init:
```
<div id="element" data-mage-init='{"collapsible":{"openedState": "active", "collapsible": true, "active": true, "collateral": { "openedState": "filter-active", "element": "body" } }}'>
    <div data-role="collapsible">
        <div data-role="trigger">
            <span>Title 1</span>
        </div>
    </div>
    <div data-role="content">Content 1</div>
</div>
```

Здесь для элемента страницы с id=element вызывается метод collapsible.

3. В файле темплейта путем вызова метода модуля средствами Магенто, используя скрипты типа text/x-magento-init:
```
<div id="one" class="foo">Hello World</div>
<div id="two" class="foo">
    Goodbye World
</div>    

<script type="text/x-magento-init">
    {
        "#one": {
            "Pulsestorm_JavascriptInitTutorial/example":{"config":"value"}          
        }
    }        
</script>
```

Здесь для DOM-элемента с id=one вызывается метод example модуля Pulsestorm_JavascriptInitTutorial.

####Файл конфигурации requirejs-config.js.

Для конфигурации модулей необходимо создать конфигурационный файл requirejs-config.js. Иерархия файлов по включению на страницах с различными зонами:
```
app/code/Package/Module/view/base/requirejs-config.js    
app/code/Package/Module/view/frontend/requirejs-config.js    
app/code/Package/Module/view/adminhtml/requirejs-config.js    
```

Пример содержимого:
```
var config = {
    map: {
        'some/newmodule': {
            'foo': 'foo1.2'
        },
        'some/oldmodule': {
            'foo': 'foo1.0'
        }
    },
    shim: {
        jqueryMask: ['jquery']
    },
    "mixins": {
        "mage/tabs": {
        'Vendor_Module/js/accordion-mixin': true
        }
    },
    paths:{
        "jquery.cookie":"Package_Module/path/to/jquery.cookie.min"
    }
};
```

- paths используется для создания алиасов
- mixins - для добавления методов в существующий модуль или модификации/дополнения его
- map - например, для загрузки различных версий модулей
- shim - для указания зависимостей модуля от других модулей