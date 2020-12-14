##UI виджеты

###Пример валидации полей формы при помощи компонента Validation.

Способы добавления правил для валидации полей формы:

1. Добавить data-mage-init аттрибут элементу form для инициализации компонента.
```
<form data-mage-init='{"validation":{}}'>
```

2. Добавить правила валидации для полей формы. Например,
```
<input data-validate='{"required":true}' name="customer_name" id="customer_name" ... />
```

Здесь мы добавили правило, что данное поле не должно быть пустым при отправке формы.

3. Или добавить правила валидации при инициализации компонента. Например,
```
<form data-mage-init='{
	"validation":{
		"rules": {
			"customer_name": {
				"required":true
			}
		}
	}
}'>
```

###Использование миксинов в UI-компонентах.

Например, для того, чтобы расширить базовую функциональность метода компонента form необходимо. 

1. Декларируем использование  миксина при создании формы. Например, в файле Your_Module/view/base/requirejs-config.js:
```
var config = {
    'config': {
        'mixins': {
            'Magento_Ui/js/form/form': {
                'Your_Module/form-hook': true
            }
        }
    }
};
```

2. Создаем сам миксин по пути Your_Module/view/base/web/form-hook.js:
```
define([], function () {
    'use strict';

    return function (Form) {
        return Form.extend({
            initialize: function () {
                this._super();
                console.log('Hello from the mixin!');
            }
        });
    }
});
```

Теперь всегда при создании формы будет отрабатывать метод initialize нашего миксина.

###Collapsible widget

В Магенто этот виджет конвертирует хэдэр/контент пару в аккордион. При клике на хэдэр контент или скрывается, или показывается.

Примеры, инициализации виджета:

1. Через аттрибут data-mage-init компонента-оббертки аккордиона. Например:

```
<div data-mage-init='{"accordion":{"openedState": "active", "collapsible": true, "active": false, "multipleCollapsible": false}}'>
    <div data-role="collapsible">
        <div data-role="trigger">
            Title 1
        </div>
    </div>
    <div data-role="content">
        <p>
            Content 1
        </p>
    </div>
    <div data-role="collapsible">
        <div data-role="trigger">
            Title 2
        </div>
    </div>
    <div data-role="content">
        <p>
            Content 2
        </p>
    </div>
</div>
```

2. При инициализации кастомного js-модуля. Например, в файле app/design/frontend/[Vendor_Name]/[Theme_Name]/web/js/custom.js.

```
define([
"jquery",
"accordion",
], function ($) {
    'use strict';
    $(document).ready(function() {
        $("#accordion").accordion({
            "active": [1, 2],
            "collapsible": true,
            "openedState": "active",
            "multipleCollapsible": true
        });
    });
});
```

Сам файл-темплейт будет содержать лишь только html структуру. Например:
```
<div id="accordion">
    <div data-role="collapsible">
        <div data-role="trigger">
            <h4>Title 1</h4>
        </div>
    </div>
    <div data-role="content">Text 1</div>
    
    <div data-role="collapsible">
        <div data-role="trigger">
            <h4>Title 2</h4>
        </div>
    </div>
    <div data-role="content">Text 2</div>
</div>
```

Опции виджета:

- active - определяет какая таба будет открыта после инициализации виджета.
  Дэфолтное значение: [0]. Другие варианты:
```
$("#element").accordion({ active: "0 1" });
$("#element").accordion({ active: [0,1] });
```
- multipleCollapsible - могут ли другие табы быть открыты одновременнно.
  Дэфолтное значение: false
  
- openOnFocus - если тайтл получает фокус, то открывается или нет его контент.
  Дэфолтное значение: false
  

Для анимации необходимо установить свойству animate значение в виде true/false, или числовое значение времени анимации, или объект со свойствами. 

Для апдэйта контента через ajax-запросы необходимо добавить аттрибут data-ajax="true" к элементу, который содержит линку на ресурс.

Например:
```
<div data-role="content" data-ajax=true ajaxUrlElement="template/content.html">Text 2</div>
```

Можно сохранить текущее состояние открытых табов виджета в локалсторидж. Для этого необходимо реализовать метод saveState.
