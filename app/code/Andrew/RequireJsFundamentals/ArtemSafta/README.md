#Require JS Fundamentals

Для добавления js файла в M2 существует нельсколько способов.

#Добавление через `xml`

В нашем `layout` добавляем ноду `<head>`, а в нее `<script>`

````
<?xml version="1.0"?>
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    <head>
        <script src="Module_Name/js/script-example.js" defer="true"/>
    </head>
</page>
````

`defer="true" `тригер, который позволяет не блокировать загрузку страницы пока грузиться на скрипт
После этого создаем файл в `view/frontend/web/js/script-example.js`


#Добавление через `phtml`

````
<?xml version="1.0"?>
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" layout="1column" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    <referenceContainer name="content">
        <block class="Magento\Framework\View\Element\Template" name="js_container" template="Module_Name::js_container.phtml" />
    </referenceContainer>
</page>
````

Далее создаем `view/frontend/templates/js_container.phtml`

````
<script type="text/x-magento-init">
	{
		"*": {
			"Module_Name/js/script-example": {}
		}
	}
</script>
````


#Декларирование `js` файла как модуль для `Require JS`

````
define(function() {
'use strict';

	return function () {
		console.log("Require JS module");
	}
});
````

#Транспортировка данных из PHP в js модуль

 `js_container.phtml` добавляем, такой код

````
<script type="text/x-magento-init">
	{
		"*": {
			"Module_Name/js/script-example": {
				"base_url": "<?= $block->escapeJs($block->getBaseUrl());"
			}
		}
	}
</script>
````



после этого добавляем `script-example.js`


````
define(function() {
'use strict';

	return function (config) {
		console.log("Require JS module with", config);
	}
});
````


Вместо звездочки можем указать определённый селектор в `js_container.phtml`

````
<script type="text/x-magento-init">
	{
		"#target": {
			"Module_Name/js/script-example": {
				"base_url": "<?= $block->escapeJs($block->getBaseUrl());"
			}
		}
	}
</script>
<div id="target"></div>
````


Добавляем в `script-example.js`

````
definte(function() {
'use strict';

	return function (config, element) {
		console.log(element);
	}
});

````

Также есть возможность добавить `js` модуль с помощью атрибута `data-mage-init`

````<div id="target" data-mage-init='{"Module_Name/js/script-example": {"base_url": "<?= $block->escapeJs($block->getBaseUrl());"}'></div>````


#Добавление зависимостей в js модуль


````
define(['jquery'], function($) {
'use strict';

	return function (config, element) {
		$.getJSON.(config.base_url + 'res/v1/directory/currency', function(result) {
			element.innerText = JSON.stringify(result, null, 2);
		});
	}
});
````


#Добавление alias к нашему js модулю

Создаем `frontend/require-config.js`

````
var config = {
    map: {
        '*': {
            coffee: 'Module_Name/js/script-example'
        }
    }
}
````

Все `require-config.js` мёрджатся. Результат мерджа можно найти в `pub/static/` в теме


Иногда возникает необходимость расширить функциональность `js` модулей в ядре для этого можно воспользоваться `mixins`

Например для `Magento_Checkout/js/checkout-data.js` результатом выполнения этого скрипта является объект.
Этот объект хранит в себе список методов.

Давайте расширим `getSelectedShippingAddress` метод

Для этого нужно создать `frontend/require-config.js`

````
var config = {
    config: {
        mixins: {
            "Magento_Checkout/js/checkout-data": {
                "Module_Name/js/checkout-data-mixin": true
            }
        }
    }
}
````

и `frontend/web/js/checkout-data-mixin.js`

````
define([], function($) {
'use strict';

	return function (checkoutData) {
		const orig = checkoutData.getSelectedShippingAddress;

		checkoutData.getSelectedShippingAddress = function() {
			const address = orig.bind(checkoutData)();
			console.log('Selected shipping address', address);
			return address;
		}
	}
});
````


Добавите `js` модуля на все страницах

Создаём `frontend/web/js/log-when-loaded.js`

````angular2html
define(function($) {
    'use strict';
    console.log('Selected shipping address', address);
});
````

Создаём `frontend/require-config.js`

````angular2html
var config = {
    config: {
        deps: ['Module_Name/js/log-when-loaded']
    }
}
````


Добавить загрузку модуля перед `Magento_Catalog/js/view/compare-products` модулем, можно использовать директиву `shim`

Пример `require-config.js`

````
var config = {
    shim: {
        "Magento_Catalog/js/view/compare-products": {
            deps: ['Module_Name/js/log-when-loaded']
        }
    }
}
````
