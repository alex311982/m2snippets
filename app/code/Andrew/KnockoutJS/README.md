#KnockoutJS

Knockout JS (KO) - это Javascript библиотека, которая применяется на фронте, для реализации MVVM-паттерна. Библиотека предназначена для привязывания данных к определенным элементам страницы.
В Мадженте это реализовывается созданием View-Model внутри файла со скриптом и темплейта. При изменении данных внутри модели они сразу же влекут изменения на странице сайта.

Пример создания knockout-приложения в Мадженте.

1. Создаем темплейт, который обрабатывается бэкэндом для инициализации приложения.
```
<?php
/* @var [Vendor]\[Module]\Block\Hello $block */ 
?> 
<div id="container" data-bind="scope: 'knockout-tutorial'"> 
<!-- ko template: getTemplate() --><!-- /ko --> 
</div> 
<script type="text/x-magento-init"> 
   { 
     "*": { 
         "Magento_Ui/js/core/app": { 
          "components": { 
           "knockout-tutorial": { 
            "component": "[Vendor]_[Module]/js/viewModel", 
             "template" : "[Vendor]_[Module]/example" 
          } 
       }
     }
   } 
 } 
</script>
```

Библиотека работает работает с двумя типами элементов - реальные элементы и виртуальные. Все элементы внутри темплейта, заключенные в <!-- ko  --> <!-- /ko -->, являются виртульными.

<!-- ko template: getTemplate() --><!-- /ko --> обозначает, что необходимо отрендерить темплейт "[Vendor]_[Module]/template" внутри элемента с id="container".

data-bind="scope: 'knockout-tutorial'" обозначает, что необходимо отрендерить компонент knockout-tutorial внутри дива.

Модуль Magento_Ui/js/core/app является контейнером для инициализации и запуска всех дочерних компонентов.

2. Создаем View-Model для реализации логики отображения и изменения данных на фронте. Например:
```
app/code/[Vendor]/[Module]/view/frontend/web/js/viewModel.js

define([ 
   'uiComponent', 
   'ko' 
], function(Component, ko) { 
    return Component.extend({ 
      clock: ko.observable(""), 
      initialize: function () { 
          this._super(); 
          setInterval(this.reloadTime.bind(this), 1000); 
      }, 
      reloadTime: function () { 
         /* Setting new time to our clock variable. DOM manipulation   will happen automatically */ 
         this.clock(Date()); 
      }, 
      getClock: function () { 
         return this.clock; 
      } 
   }); 
});
```

Для создания своего компонента необходимо расширить функциональность базового компонента (uiComponent), который был заинжектен в наш компонент.
Дальше библиотека вызывает метод-инициализации компонента - initialize. 
Внутри нашего модуля определяем переменную clock, значение которой будет меняться каждую секунду, и отображаться на фронте. 
Для того, чтобы можно было следить за значением этой переменной, делаем ее наблюдаемым объектом средствами библиотеки: clock: ko.observable("").
Теперь подписчики на изменения этой переменной могут автоматически отслеживать ее изменения.
Модуль обязательно должен возвращать расширенный компонент.

3. Создаем файл с темплейтом. Это простой по структуре темплейт для рендеринга на фронте текущего значения наблюдаемой переменной clock.
```
app/code/[Vendor]/[Module]/view/frontend/web/template/template.html

<h1 data-bind="text: getClock()"></h1>
```

data-bind позволяет привязать слушателя к наблюдаемому объекту.

###Работа с observableArray

Допустим, вам надо динамически изменять массив, на который подписаны какие-то обработчики (или интерфейс). 
апример, вы получили новую партию данных с сервера и в цикле добавляете их в массив.
Если сделать все в лоб — то обработчик изменения отработает столько раз, сколько элементов вы добавите. И это скажется на производительности.
Так делать не надо:
```
var items = ko.observableArray([]);

for (var i = 0, j = newData.length; i < j; i++) {
    items.push( newData[i] );
}
```

Лучше так:
```
var items = ko.observableArray([]);
// Получим чистый массив элементов.
var underlyingArray = items();
for (var i = 0, j = newData.length; i < j; i++) {
    // Добавляем новые элементы как обычно.
    underlyingArray.push( newData[i] );
}
// Говорим, что наш массив изменился и стоит обновить интерфейс и вызвать обработчики.
self.items.valueHasMutated();
```

###Вычисляемые объекты. Функция ko.computed

К примеру, необходимо следить сразу за двумя связанныит переменными в темплейте. Например, за полным именем человека, состоящего из фамилии и имени.
Для этого комбинируем их и обворачиваем единой функцией для слежения изменения состояния. Наш компонент:
```
var ViewModel = function(first, last) {
    this.firstName = ko.observable(first);
    this.lastName = ko.observable(last);
    var self = this;
    
    this.fullName = function(separator) {
        return ko.computed(function () {
            return self.firstName() + separator + self.lastName();}, this);
    };
};
```

И темплейт:
```
<h1 data-bind="text: fullName()"></h1>
```

###Knockout-ES5

Данная библиотека https://github.com/SteveSanderson/knockout-es5 позвлдяет следить за свойствами объекта без указания, что они наблюдаемые.
Для этого используется вызов метода ko.track внутри объекта. После этого все свойства стают быть наблюдаемыми и можно применять к ним сеттеры и геттеры как в ES5.

Например, есть функция для создания объектов со свойствами:
```
function Test(data) {
    this.item = data.item;
    this.price = data.price;
    this.quantity = data.quantity;
 
    this.getSubtotal = function() {
        return "$" + (this.price * this.quantity).toFixed(2);
    }
 
    // Instead of declaring ko.observable properties, we just have one call to ko.track 
    ko.track(this);
}
```
Теперь можно обратиться к свойству объекта через сеттер/геттер:
```
someOrderLine.quantity += 1;
```

###uiRegistry

Модуль Магенто для регистрации и дальнейшего доступа к созданных на данный момент компонентов. Используется в глобальном пространстве. Например:
```
var component = require('uiRegistry').get('componentName');
```

Можно искать компонент по содержащемуся в нем свойству с определенным значением.
```
var component = registry.get('property = propertyValue');
```

Для получения списка всех компонентов на странице:
```
require('uiRegistry').get(function(component){console.log(component.name)});
```

Чтобы получить ссылку на компонент по цепочки связанных компонентов:
```
var fieldName = registry.get('product_form.product_form.product-details.container_name.name');
```
