##Di
Классы в мадженто имеют зависимости. Строки, числа, объекты ...  являются зависимостями.

В magento 2 реализовaно автоматическое создание объектов в конструкторе.
За это отвечает `Magento\Framework\App\ObjectManager`.
Реализовано на основе паттерна `Dependency Injection Container DIC`.

Когда создаётся объект класса, создаётся все дерево зависимостей. Дерево объектов с их зависимостями называется  называется `Object Graph`.

Object manager конфигурируется с помощью xml конфигурации `(di.xml)`

В `di.xml` для ноды `Type` есть атрибут `shared`

Он регулирует как будет браться объект. С помощью `objectManager->create()` или `->get()`

То есть мы можем регулировать поведение создания объектов. По дефолту стоит `true`.

Cуществует `injectable` и `non-injectable` классы.

Модели чаще всего `none-injectable` классы. Когда мы их доавляем в конструктор и они создаются они пустые без данных. 

Перед тем как использовать такие классы они должны быть загружены данными. Например с помощью метода `->load($id)`. 

Так же эти классы имеют поведение `non-shared`.
`Non-injectable` классы технически могут быть добавлены в конструктор.

Каждый класс который добавляется в конструктор должен инициализроваться полностью (должен быть готов к использованию) и должен быть `shared` объектом.

Для продакшена не рекомендуется использовать `objectManager`. Исключениями я вляется `интеграционные тесты`, `locators`, `обратная совместимость`, `фабрики`.

Для инициализации `non-injectable` классов используют фабрики. Достаточно в конце класса добавить суфикс `Factory`.
Этот суффикс можно добавить к любому классу.

В `di.xml` можно  подменить свойство класса, в котором будет храниться такие типы:
`objects`, `string`,`constant`,`array`, `number`,`boolean`, `null`, `init_parameter`





