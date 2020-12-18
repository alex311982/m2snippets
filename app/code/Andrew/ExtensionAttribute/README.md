#Extension Attribute

Сравнение Extension Attribute и Custom Extension

![Alt text](./extension_attribute.png?raw=true "ea")

Extension Attribute создается в `extension_attributes.xml` файле

В нодe `<extension_attribute>` в атрибуте `for` указываем интерефейс для которого будем создавать атрибут

В ноде `<attribute>`
В атрибуте `code`  указываем код нашего атрибута, а в атрибуте `type` указываем тип нашего атрибута.

Тип бывает скалярные и не скалярные. Для скалярных есть еще нода `<join>`

Типы:

- string
- array
- int
- object (указываем интерфейс)

Интерфейс должен обладать методом `getExtensionAttributes` и должен нам возвращать интерфейс (для продукта `ProductExtensionInterface`, который реализует  `\Magento\Framework\Api\ExtensionAttributesInterface`)

Extension Attribute должен быть заполнен программно. Например с помощью плагина. Чаще всего необходимо написать плагин на `Repository`, если нет репозиторя, то можна написать плагин или повеситься на событие

Extension Attribute позволяет объеденять поля для атрибута, это касается только скалярных типов.

Это можно указать в ноде `<join>`
Атрибуты ноды `<join>`

- reference_table в какой таблице наше значение будет сохранено
- reference_field поле, с помощью которого происходят связь с главной сущностью
- join_on_field поле, которое характеризует главную нашу сущность

В ноде `<field>` указываем название поля в `reference_table`, где будет храниться наше значение

Главное преимущество Extension Attribute в том, что можно создавать комплексные атрибуты

Алгоритм создания скаларяных атрибутов

- Создать таблицу, в которой будет храниться наше значение атрибута
- Создать классы которые будут отвечать за загрузку и сохранение нашего Extension Attribute
- Создать декларацию в  `etc/extension_attributes.xml`
- Создать плагин для заполнения значений. Иногда использовать `<join>`

Алгоритм создания  нескаларяных атрибутов

- Создать PHP Interface, содержащий геттер для получения значения атрибута
- Создать реализацию для этого интерфейса
- Создать `preference` для интерфейса и реализации
- Сконфигурировать `extension_attributes.xml`
- Создать плагин. Например на `getList`, для заполнения значений, так как нельзя использовать для нескалярных `<join>`

#Создание расширяемой кастомной модели

- `\Magento\Framework\Api\ExtensibleDataInterface` наша модель должна реализовывать этот интерфейс
- Добавить методы `getExtensionAttributes` и `setExtensionAttributes` в наш интерфейс   
- Реализовать методы `getExtensionAttributes` и `setExtensionAttributes`
- Опционально вызывать `\Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface::process` в методах репозитория, которые используют коллекции в случае использования `<join>`



