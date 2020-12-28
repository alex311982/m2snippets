#Создание кастомного связывания в KnockoutJS

Элементы биндинга в темплейтах можно расширять кастомными. Есть стандартные элементы - click, value, text ...
К примеру, необходимо связать одно из свойств viewModel с выбранным в селекте значением и наоборот.

Для этого создаем темплейт с указанием параметров биндинга:

```
<select data-bind="selectedDataAttribute: { bind: 'data-country', to: 'country' }">
    <option data-country="Belgium" value="1">Brussels</option>
    <option data-country="United Kingdom" value="2">London</option>
    <option data-country="France" value="3">Paris</option>
</select>
```

Здесь: 

- selectedDataAttribute - кастомный биндинг элемент
- bind: 'data-country' - откуда брать значение для значения параметра viewModel
- to: 'country' - название параметра viewModel для установки значения после изменения состояния кастомного селекта

Пример реализации кастомного биндинг элемента:

```
ko.bindingHandlers.selectedDataAttribute = {
    init: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
        var dataAttribute = ko.utils.unwrapObservable(valueAccessor().bind);
        var viewModelProperty = valueAccessor().to;
        var dataAttributeValue = $(element).find(':selected').attr(dataAttribute);
        viewModel[viewModelProperty](dataAttributeValue);
        $(element).change(function() {
            var dataAttribute = ko.utils.unwrapObservable(valueAccessor().bind);
            var viewModelProperty = valueAccessor().to;
            var dataAttributeValue = $(element).find(':selected').attr(dataAttribute);
            viewModel[viewModelProperty](dataAttributeValue);
        });
    },
    update: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
        var dataAttribute = ko.utils.unwrapObservable(valueAccessor().bind);
        var viewModelProperty = valueAccessor().to;
        var value = viewModel[viewModelProperty]();
        $("option", element).filter(function(option) { return $(option).attr(dataAttribute) === value; }).prop("selected", "selected");
    }
}
```

У модели кастомного биндинг элемента есть 2 метода реализация которых есть необязательным:

- init - выполняется после первого биндинга элемента с параметром viewModel
- update - после апдейта связаного параметра viewModel с DOM-элементом


Что происходит в методе init:

- берется имя аттрибута (data-country в нашем случае), берется имя связанной переменной во viewModel, берется значение аттрибута и сетится связанной переменной.
- отрабатывается метод init при смене выбранного элемента в селекте
- при изменении значения связанного аттрибута во viewModel происходит обратное изменение выбранного элемента в селекте

###Связывание в виртульных элементах KnockoutJS

При работе с виртуальными элементами необходимо непосредственно указывать биндинг элементу, что он будет работать с виртуальными элементами.
Например, необходимо в рандомном порядке вывести элементы темплейта:
```
<!-- ko randomOrder: true -->
    <div>First</div>
    <div>Second</div>
    <div>Third</div>
<!-- /ko -->
```

Если реализовать биндинг-элемент без учета виртуальности нашего темплейта, то будет ошибка The binding 'randomOrder' cannot be used with virtual elements.
```
ko.bindingHandlers.randomOrder = {
    init: function(elem, valueAccessor) {
        // Build an array of child elements
        var child = ko.virtualElements.firstChild(elem),
            childElems = [];
        while (child) {
            childElems.push(child);
            child = ko.virtualElements.nextSibling(child);
        }
 
        // Remove them all, then put them back in a random order
        ko.virtualElements.emptyNode(elem);
        while(childElems.length) {
            var randomIndex = Math.floor(Math.random() * childElems.length),
                chosenChild = childElems.splice(randomIndex, 1);
            ko.virtualElements.prepend(elem, chosenChild[0]);
        }
    }
};
```

Что необходимо изменить:
1. Разрешить работать с виртуальными элементами:
```
ko.virtualElements.allowedBindings.randomOrder = true;
```
2. Изменить вызов методов firstChild, nextSibling, emptyNode, как вызов на виртуальных элементах:

```
ko.bindingHandlers.randomOrder = {
    init: function(elem, valueAccessor) {
        // Build an array of child elements
        var child = ko.virtualElements.firstChild(elem),
            childElems = [];
        while (child) {
            childElems.push(child);
            child = ko.virtualElements.nextSibling(child);
        }
 
        // Remove them all, then put them back in a random order
        ko.virtualElements.emptyNode(elem);
        while(childElems.length) {
            var randomIndex = Math.floor(Math.random() * childElems.length),
                chosenChild = childElems.splice(randomIndex, 1);
            ko.virtualElements.prepend(elem, chosenChild[0]);
        }
    }
};
```